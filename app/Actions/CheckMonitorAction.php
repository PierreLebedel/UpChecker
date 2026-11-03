<?php

namespace App\Actions;

use App\Enums\AlertTransition;
use App\Enums\CheckStatus;
use App\Enums\MonitorCheckCriterionType;
use App\Enums\MonitorStatus;
use App\Events\MonitorCheckCompleted;
use App\Jobs\CheckMonitorJob;
use App\Models\CheckResult;
use App\Models\Monitor;
use App\Models\User;
use App\Notifications\MonitorAlertNotification;
use Carbon\CarbonInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use JsonException;
use Throwable;

class CheckMonitorAction
{
    public function handle(Monitor $monitor): CheckResult
    {
        $checkedAt = now();
        $startedAt = microtime(true);
        $httpStatus = null;
        $responseTimeMs = null;
        $responseExcerpt = null;

        try {
            $response = Http::timeout($monitor->timeout_seconds)
                ->connectTimeout($monitor->timeout_seconds)
                ->withOptions(['allow_redirects' => true])
                ->withUserAgent('UpChecker/1.0.0')
                ->get($monitor->url);

            $responseTimeMs = (int) round((microtime(true) - $startedAt) * 1000);
            $httpStatus = $response->status();
            $responseExcerpt = $this->excerpt($response->body());
            [$status, $errorMessage] = $this->evaluateResponse($monitor, $response);
        } catch (ConnectionException $exception) {
            $status = CheckStatus::Timeout;
            $errorMessage = $exception->getMessage();
        } catch (Throwable $exception) {
            $status = CheckStatus::Invalid;
            $errorMessage = $exception->getMessage();
        }

        return $this->recordResult(
            monitor: $monitor,
            status: $status,
            checkedAt: $checkedAt,
            httpStatus: $httpStatus,
            responseTimeMs: $responseTimeMs,
            errorMessage: $errorMessage,
            responseExcerpt: $responseExcerpt,
        );
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
     * @param  array<string, mixed>  $criterion
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
        $monitor->loadMissing('project.user');
        $user = $monitor->project->user;
        $checkedUrl = $monitor->url;

        $result = DB::transaction(function () use ($monitor, $user, $status, $checkedAt, $httpStatus, $responseTimeMs, $errorMessage, $responseExcerpt, $checkedUrl): CheckResult {
            $user = User::query()->lockForUpdate()->findOrFail($user->id);
            $monitor->refresh();
            $completedAt = now();
            $wasConfirmed = $monitor->failure_confirmed_at !== null;
            $isFirstFailure = $status->isFailure() && $monitor->failure_detected_at === null;
            $transition = null;

            if ($status === CheckStatus::Up) {
                $monitor->failure_detected_at = null;
                $monitor->failure_confirmed_at = null;
                $transition = $wasConfirmed ? AlertTransition::DownToUp : null;
            } elseif ($isFirstFailure) {
                $monitor->forceFill(['failure_detected_at' => $completedAt]);
            } elseif (! $wasConfirmed && $checkedAt->greaterThanOrEqualTo($monitor->failure_detected_at->copy()->addMinute())) {
                $monitor->forceFill(['failure_confirmed_at' => $completedAt]);
                $transition = AlertTransition::UpToDown;
            }

            $result = CheckResult::create([
                'monitor_id' => $monitor->id,
                'status' => $status,
                'http_status' => $httpStatus,
                'response_time_ms' => $responseTimeMs,
                'error_message' => $errorMessage,
                'checked_url' => $checkedUrl,
                'checked_at' => $checkedAt,
                'response_excerpt' => $responseExcerpt,
            ]);

            $monitor->forceFill([
                'current_status' => $this->monitorStatusFor($status),
                'last_checked_at' => $checkedAt,
                'last_success_at' => $status === CheckStatus::Up ? $checkedAt : $monitor->last_success_at,
                'last_failure_at' => $status->isFailure() ? $checkedAt : $monitor->last_failure_at,
                'next_check_at' => $status->isFailure() && $monitor->failure_confirmed_at === null
                    ? $monitor->failure_detected_at->copy()->addMinute()
                    : $completedAt->copy()->addMinutes($monitor->interval_minutes),
            ])->save();

            if ($isFirstFailure) {
                CheckMonitorJob::dispatch($monitor->id, $monitor->failure_detected_at->toIso8601String())
                    ->delay($monitor->next_check_at)
                    ->afterCommit();
            }

            $this->updateReminderSchedule($user, $completedAt);

            if ($transition !== null) {
                $this->sendAlertNotification($monitor, $user, $result, $transition, $completedAt);
            }

            return $result;
        });

        broadcast(new MonitorCheckCompleted(
            monitorId: $monitor->id,
            userId: (string) $monitor->project()->value('user_id'),
            checkResultId: $result->id,
            status: $status->value,
            checkedAt: $checkedAt->toIso8601String(),
        ));

        return $result;
    }

    private function sendAlertNotification(
        Monitor $monitor,
        User $user,
        CheckResult $result,
        AlertTransition $transition,
        CarbonInterface $checkedAt,
    ): void {
        $notification = new MonitorAlertNotification($result, $transition);

        if ($notification->via($user) === []) {
            return;
        }

        $user->notify($notification->afterCommit());

        $monitor->forceFill([
            'last_alerted_at' => $checkedAt,
        ])->save();
    }

    private function updateReminderSchedule(User $user, CarbonInterface $completedAt): void
    {
        $hasConfirmedFailures = Monitor::query()
            ->confirmedFailures()
            ->whereRelation('project', 'user_id', $user->id)
            ->exists();

        $user->forceFill([
            'next_alert_reminder_at' => $hasConfirmedFailures
                ? ($user->next_alert_reminder_at ?? $completedAt->copy()->addHour())
                : null,
        ])->save();
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
