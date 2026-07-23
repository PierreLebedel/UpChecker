<?php

namespace App\Actions;

use App\Enums\CheckStatus;
use App\Enums\MonitorCheckCriterionType;
use App\Enums\MonitorStatus;
use App\Events\MonitorCheckCompleted;
use App\Models\CheckResult;
use App\Models\Monitor;
use Carbon\CarbonInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use JsonException;
use Throwable;

class CheckMonitorAction
{
    public function handle(Monitor $monitor): CheckResult
    {
        $checkedAt = now();
        $startedAt = microtime(true);

        try {
            $response = Http::timeout($monitor->timeout_seconds)
                ->connectTimeout($monitor->timeout_seconds)
                ->withOptions(['allow_redirects' => true])
                ->get($monitor->url);

            $responseTimeMs = (int) round((microtime(true) - $startedAt) * 1000);
            [$status, $errorMessage] = $this->evaluateResponse($monitor, $response);

            return $this->recordResult(
                monitor: $monitor,
                status: $status,
                checkedAt: $checkedAt,
                httpStatus: $response->status(),
                responseTimeMs: $responseTimeMs,
                errorMessage: $errorMessage,
                responseExcerpt: $this->excerpt($response->body()),
            );
        } catch (ConnectionException $exception) {
            return $this->recordResult(
                monitor: $monitor,
                status: CheckStatus::Timeout,
                checkedAt: $checkedAt,
                errorMessage: $exception->getMessage(),
            );
        } catch (Throwable $exception) {
            return $this->recordResult(
                monitor: $monitor,
                status: CheckStatus::Invalid,
                checkedAt: $checkedAt,
                errorMessage: $exception->getMessage(),
            );
        }
    }

    /**
     * @return array{0: CheckStatus, 1: string|null}
     */
    private function evaluateResponse(Monitor $monitor, Response $response): array
    {
        $payload = null;
        $jsonDecoded = false;

        foreach ($monitor->check_criteria ?: Monitor::defaultCheckCriteria() as $criterion) {
            $type = MonitorCheckCriterionType::tryFrom((string) ($criterion['type'] ?? ''));

            if ($type === null) {
                return [CheckStatus::Invalid, 'Un critere de verification est invalide.'];
            }

            $result = match ($type) {
                MonitorCheckCriterionType::HttpStatus => $this->evaluateHttpStatusCriterion($criterion, $response),
                MonitorCheckCriterionType::BodyContains => $this->evaluateBodyContainsCriterion($criterion, $response),
                MonitorCheckCriterionType::JsonPath => $this->evaluateJsonPathCriterion($criterion, $response, $payload, $jsonDecoded),
            };

            if ($result[0] !== CheckStatus::Up) {
                return $result;
            }
        }

        return [CheckStatus::Up, null];
    }

    /**
     * @return array{0: CheckStatus, 1: string|null}
     */
    private function evaluateHttpStatusCriterion(array $criterion, Response $response): array
    {
        $expectedStatus = (int) ($criterion['expected'] ?? 200);

        if ($response->status() !== $expectedStatus) {
            return [
                CheckStatus::Down,
                "Statut HTTP {$response->status()} recu au lieu de {$expectedStatus}.",
            ];
        }

        return [CheckStatus::Up, null];
    }

    /**
     * @param  array<string, mixed>  $criterion
     * @return array{0: CheckStatus, 1: string|null}
     */
    private function evaluateBodyContainsCriterion(array $criterion, Response $response): array
    {
        $expectedText = (string) ($criterion['text'] ?? '');

        if ($expectedText === '' || ! str_contains($response->body(), $expectedText)) {
            return [
                CheckStatus::Down,
                'Le contenu attendu est absent de la reponse.',
            ];
        }

        return [CheckStatus::Up, null];
    }

    /**
     * @param  array<string, mixed>  $criterion
     * @param  array<mixed>|null  $payload
     * @return array{0: CheckStatus, 1: string|null}
     */
    private function evaluateJsonPathCriterion(array $criterion, Response $response, ?array &$payload, bool &$jsonDecoded): array
    {
        $path = (string) ($criterion['path'] ?? '');

        if (! $jsonDecoded) {
            try {
                $decodedPayload = json_decode($response->body(), true, flags: JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                return [CheckStatus::Invalid, 'La reponse attendue en JSON est invalide.'];
            }

            if (! is_array($decodedPayload)) {
                return [CheckStatus::Invalid, 'La reponse JSON ne contient pas un objet exploitable.'];
            }

            $payload = $decodedPayload;
            $jsonDecoded = true;
        }

        if ($path === '' || ! Arr::has($payload, $path)) {
            return [
                CheckStatus::Invalid,
                "La cle JSON attendue \"{$path}\" est absente.",
            ];
        }

        if (! array_key_exists('expected', $criterion) || $criterion['expected'] === null || $criterion['expected'] === '') {
            return [CheckStatus::Up, null];
        }

        $actualValue = data_get($payload, $path);

        if (! is_scalar($actualValue) && $actualValue !== null) {
            return [
                CheckStatus::Invalid,
                "La valeur JSON \"{$path}\" n'est pas scalaire.",
            ];
        }

        if ((string) $actualValue !== (string) $criterion['expected']) {
            return [
                CheckStatus::Down,
                "La valeur JSON \"{$path}\" ne correspond pas a la valeur attendue.",
            ];
        }

        return [CheckStatus::Up, null];
    }

    private function recordResult(
        Monitor $monitor,
        CheckStatus $status,
        CarbonInterface $checkedAt,
        ?int $httpStatus = null,
        ?int $responseTimeMs = null,
        ?string $errorMessage = null,
        ?string $responseExcerpt = null,
    ): CheckResult {
        $result = CheckResult::create([
            'monitor_id' => $monitor->id,
            'status' => $status,
            'http_status' => $httpStatus,
            'response_time_ms' => $responseTimeMs,
            'error_message' => $errorMessage,
            'checked_url' => $monitor->url,
            'checked_at' => $checkedAt,
            'response_excerpt' => $responseExcerpt,
        ]);

        $monitor->forceFill([
            'current_status' => $this->monitorStatusFor($status),
            'last_checked_at' => $checkedAt,
            'last_success_at' => $status === CheckStatus::Up ? $checkedAt : $monitor->last_success_at,
            'last_failure_at' => $status->isFailure() ? $checkedAt : $monitor->last_failure_at,
            'next_check_at' => $checkedAt->copy()->addMinutes($monitor->interval_minutes),
        ])->save();

        broadcast(new MonitorCheckCompleted(
            monitorId: $monitor->id,
            userId: (string) $monitor->project()->value('user_id'),
            checkResultId: $result->id,
            status: $status->value,
            checkedAt: $checkedAt->toIso8601String(),
        ));

        return $result;
    }

    private function monitorStatusFor(CheckStatus $status): MonitorStatus
    {
        return match ($status) {
            CheckStatus::Up => MonitorStatus::Up,
            CheckStatus::Down => MonitorStatus::Down,
            CheckStatus::Timeout => MonitorStatus::Timeout,
            CheckStatus::Invalid => MonitorStatus::Invalid,
        };
    }

    private function excerpt(string $body): string
    {
        return mb_substr($body, 0, 10000);
    }
}
