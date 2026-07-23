<?php

use App\Enums\CheckStatus;
use App\Enums\MonitorCheckCriterionType;
use App\Jobs\CheckMonitorJob;
use App\Models\CheckResult;
use App\Models\Monitor;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Détail URL')] class extends Component
{
    use WithPagination;

    private const int SparklineCheckResultLimit = 150;

    public string $monitor;

    public bool $includeSuccessfulCheckResults = true;

    public bool $showEditMonitorModal = false;

    public ?string $selectedMonitorId = null;

    public string $monitorName = '';

    public string $url = '';

    public bool $enabled = true;

    public int $intervalMinutes = 5;

    public int $timeoutSeconds = 10;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $checkCriteria = [];

    public function mount(string $monitor): void
    {
        $this->monitor = $monitor;
    }

    /**
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        return [
            "echo-private:monitors.{$this->monitor},MonitorCheckCompleted" => 'refreshAfterMonitorCheckCompleted',
        ];
    }

    #[Computed]
    public function currentMonitor(): Monitor
    {
        return Monitor::query()
            ->with('project')
            ->whereKey($this->monitor)
            ->whereHas('project', fn ($query) => $query->whereBelongsTo(Auth::user()))
            ->firstOrFail();
    }

    /**
     * @return LengthAwarePaginator<int, CheckResult>
     */
    #[Computed]
    public function checkResults(): LengthAwarePaginator
    {
        return $this->currentMonitor
            ->checkResults()
            ->when(! $this->includeSuccessfulCheckResults, fn ($query) => $query->where('status', '!=', CheckStatus::Up->value))
            ->latest('checked_at')
            ->paginate(10);
    }

    /**
     * @return Collection<int, CheckResult>
     */
    #[Computed]
    public function chartCheckResults(): Collection
    {
        return $this->currentMonitor
            ->checkResults()
            ->latest('checked_at')
            ->limit(self::SparklineCheckResultLimit)
            ->get()
            ->sortBy('checked_at')
            ->values();
    }

    /**
     * @param  array<string, string>  $event
     */
    public function refreshAfterMonitorCheckCompleted(array $event = []): void
    {
        Flux::toast(variant: 'success', text: 'Vérification terminée.');

        if (($event['monitorId'] ?? $this->monitor) !== $this->monitor) {
            return;
        }

        unset($this->currentMonitor, $this->checkResults, $this->chartCheckResults);
    }

    public function checkNow(): void
    {
        $monitor = $this->currentMonitor;

        $monitor->forceFill([
            'next_check_at' => now()->addMinutes($monitor->interval_minutes),
        ])->save();

        CheckMonitorJob::dispatch($monitor->id);

        unset($this->currentMonitor);

        Flux::toast(variant: 'success', text: 'Vérification lancée.');
    }

    public function updatedIncludeSuccessfulCheckResults(): void
    {
        $this->resetPage();

        unset($this->checkResults);
    }

    public function openEditMonitorModal(string $monitorId): void
    {
        $monitor = $this->ownedMonitor($monitorId);

        if (! $monitor instanceof Monitor) {
            return;
        }

        $this->selectedMonitorId = $monitor->id;
        $this->monitorName = $monitor->name;
        $this->url = $monitor->url;
        $this->enabled = $monitor->enabled;
        $this->intervalMinutes = $monitor->interval_minutes;
        $this->timeoutSeconds = $monitor->timeout_seconds;
        $this->checkCriteria = $monitor->check_criteria ?: Monitor::defaultCheckCriteria();
        $this->resetValidation();
        $this->showEditMonitorModal = true;
    }

    public function updateMonitor(): void
    {
        $monitor = $this->ownedMonitor($this->selectedMonitorId);

        if (! $monitor instanceof Monitor) {
            return;
        }

        $this->validate($this->monitorRules());

        $monitor->update($this->monitorAttributes());

        unset($this->currentMonitor, $this->checkResults, $this->chartCheckResults);

        $this->showEditMonitorModal = false;
        $this->resetMonitorForm();

        Flux::toast(variant: 'success', text: 'URL modifiée.');
    }

    public function selectedMonitor(): ?Monitor
    {
        return $this->selectedMonitorId ? $this->ownedMonitor($this->selectedMonitorId) : null;
    }

    public function addCriterion(string $type): void
    {
        $criterionType = MonitorCheckCriterionType::tryFrom($type);

        if (! $criterionType instanceof MonitorCheckCriterionType) {
            return;
        }

        if ($criterionType === MonitorCheckCriterionType::HttpStatus && $this->hasHttpStatusCriterion()) {
            return;
        }

        $this->checkCriteria[] = match ($criterionType) {
            MonitorCheckCriterionType::HttpStatus => [
                'type' => $criterionType->value,
                'expected' => 200,
            ],
            MonitorCheckCriterionType::JsonPath => [
                'type' => $criterionType->value,
                'path' => '',
                'expected' => '',
            ],
            MonitorCheckCriterionType::BodyContains => [
                'type' => $criterionType->value,
                'text' => '',
            ],
        };
    }

    public function removeCriterion(int $index): void
    {
        if (! array_key_exists($index, $this->checkCriteria)) {
            return;
        }

        unset($this->checkCriteria[$index]);
        $this->checkCriteria = array_values($this->checkCriteria);
    }

    public function hasHttpStatusCriterion(): bool
    {
        return collect($this->checkCriteria)
            ->contains(fn (array $criterion): bool => ($criterion['type'] ?? null) === MonitorCheckCriterionType::HttpStatus->value);
    }

    public function lastCheckedAt(): ?DateTimeInterface
    {
        return $this->currentMonitor->last_checked_at;
    }

    public function nextCheckAt(): ?DateTimeInterface
    {
        if ($this->currentMonitor->next_check_at === null) {
            return null;
        }

        return $this->currentMonitor->next_check_at;
    }

    public function criterionLabel(array $criterion): string
    {
        $type = MonitorCheckCriterionType::tryFrom((string) ($criterion['type'] ?? ''));

        if ($type === MonitorCheckCriterionType::JsonPath) {
            $label = (string) ($criterion['path'] ?? '');
            $expected = (string) ($criterion['expected'] ?? '');

            return $expected === '' ? $label : "{$label} = {$expected}";
        }

        return match ($type) {
            MonitorCheckCriterionType::HttpStatus => 'HTTP '.($criterion['expected'] ?? 200),
            MonitorCheckCriterionType::BodyContains => 'Contient "'.($criterion['text'] ?? '').'"',
            default => 'Critère invalide',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function monitorRules(): array
    {
        return [
            'monitorName' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'starts_with:http://,https://', 'max:2048'],
            'enabled' => ['boolean'],
            'intervalMinutes' => ['required', 'integer', Rule::in([1, 2, 5, 15, 30, 60])],
            'timeoutSeconds' => ['required', 'integer', 'min:1', 'max:60'],
            'checkCriteria' => ['required', 'array', 'min:1'],
            'checkCriteria.*.type' => ['required', 'string', Rule::enum(MonitorCheckCriterionType::class)],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function monitorAttributes(): array
    {
        return [
            'name' => $this->monitorName,
            'url' => $this->url,
            'enabled' => $this->enabled,
            'interval_minutes' => $this->intervalMinutes,
            'timeout_seconds' => $this->timeoutSeconds,
            'check_criteria' => $this->normalizedCheckCriteria(),
            'next_check_at' => now(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizedCheckCriteria(): array
    {
        if ($this->checkCriteria === []) {
            throw ValidationException::withMessages([
                'checkCriteria' => 'Ajoutez au moins un critère de vérification.',
            ]);
        }

        $normalizedCriteria = [];
        $hasHttpStatus = false;

        foreach (array_values($this->checkCriteria) as $index => $criterion) {
            $type = MonitorCheckCriterionType::tryFrom((string) ($criterion['type'] ?? ''));

            if (! $type instanceof MonitorCheckCriterionType) {
                throw ValidationException::withMessages([
                    "checkCriteria.{$index}.type" => 'Ce critère est invalide.',
                ]);
            }

            if ($type === MonitorCheckCriterionType::HttpStatus) {
                if ($hasHttpStatus) {
                    throw ValidationException::withMessages([
                        'checkCriteria' => 'Le code HTTP ne peut être défini qu’une seule fois.',
                    ]);
                }

                $expected = (int) ($criterion['expected'] ?? 0);

                if ($expected < 100 || $expected > 599) {
                    throw ValidationException::withMessages([
                        "checkCriteria.{$index}.expected" => 'Le code HTTP attendu doit être compris entre 100 et 599.',
                    ]);
                }

                $normalizedCriteria[] = [
                    'type' => $type->value,
                    'expected' => $expected,
                ];
                $hasHttpStatus = true;

                continue;
            }

            if ($type === MonitorCheckCriterionType::JsonPath) {
                $path = trim((string) ($criterion['path'] ?? ''));

                if ($path === '') {
                    throw ValidationException::withMessages([
                        "checkCriteria.{$index}.path" => 'Renseignez le champ JSON attendu.',
                    ]);
                }

                $normalizedCriteria[] = [
                    'type' => $type->value,
                    'path' => $path,
                    'expected' => trim((string) ($criterion['expected'] ?? '')),
                ];

                continue;
            }

            $text = trim((string) ($criterion['text'] ?? ''));

            if ($text === '') {
                throw ValidationException::withMessages([
                    "checkCriteria.{$index}.text" => 'Renseignez le texte attendu.',
                ]);
            }

            $normalizedCriteria[] = [
                'type' => $type->value,
                'text' => $text,
            ];
        }

        return $normalizedCriteria;
    }

    private function ownedMonitor(?string $monitorId): ?Monitor
    {
        if ($monitorId === null) {
            return null;
        }

        return Monitor::query()
            ->whereKey($monitorId)
            ->whereHas('project', fn ($query) => $query->whereBelongsTo(Auth::user()))
            ->first();
    }

    private function resetMonitorForm(): void
    {
        $this->selectedMonitorId = null;
        $this->monitorName = '';
        $this->url = '';
        $this->enabled = true;
        $this->intervalMinutes = 5;
        $this->timeoutSeconds = 10;
        $this->checkCriteria = Monitor::defaultCheckCriteria();
        $this->resetValidation();
    }
}; ?>

<section class="flex w-full flex-col gap-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('dashboard')" separator="slash">Home</flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="route('projects.index')" separator="slash">Projets</flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="route('projects.show', $this->currentMonitor->project)" separator="slash">{{ $this->currentMonitor->project->name }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item separator="slash">{{ $this->currentMonitor->name }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">

            

            <div class="mt-4 flex flex-wrap items-center gap-3">
                <flux:heading size="xl">{{ $this->currentMonitor->name }}</flux:heading>
                <flux:badge variant="solid" :color="$this->currentMonitor->current_status->color()">
                    {{ $this->currentMonitor->current_status->label() }}
                </flux:badge>
                @unless ($this->currentMonitor->enabled)
                    <flux:badge color="zinc">Désactivé</flux:badge>
                @endunless
            </div>

            <flux:text class="mt-1 break-all">{{ $this->currentMonitor->url }}</flux:text>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:button icon="pencil-square" wire:click="openEditMonitorModal('{{ $this->monitor }}')">
                Modifier
            </flux:button>

            <flux:button icon="arrow-path" wire:click="checkNow">
                Vérifier maintenant
            </flux:button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
        <flux:card class="lg:col-span-2">
            <div class="flex flex-col gap-4">
                <div>
                    <flux:heading size="lg">Statut actuel</flux:heading>
                </div>

                <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <div class="text-zinc-500 dark:text-zinc-400">Dernière vérification</div>
                        <x-relative-time :date="$this->lastCheckedAt()" class="mt-1 block font-medium text-zinc-900 dark:text-zinc-100" />
                    </div>
                    <div>
                        <div class="text-zinc-500 dark:text-zinc-400">Prochaine vérification</div>
                        <x-relative-time :date="$this->nextCheckAt()" fallback="Non planifiée" due class="mt-1 block font-medium text-zinc-900 dark:text-zinc-100" />
                    </div>
                    <div>
                        <div class="text-zinc-500 dark:text-zinc-400">Dernier succès</div>
                        <x-relative-time :date="$this->currentMonitor->last_success_at" class="mt-1 block font-medium text-zinc-900 dark:text-zinc-100" />
                    </div>
                    <div>
                        <div class="text-zinc-500 dark:text-zinc-400">Dernier échec</div>
                        <x-relative-time :date="$this->currentMonitor->last_failure_at" class="mt-1 block font-medium text-zinc-900 dark:text-zinc-100" />
                    </div>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex flex-col gap-4">
                <flux:heading size="lg">Vérification</flux:heading>

                <div class="grid gap-3 text-sm">
                    <div>
                        <div class="text-zinc-500 dark:text-zinc-400">Intervalle</div>
                        <div class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $this->currentMonitor->interval_minutes }} min</div>
                    </div>
                    <div>
                        <div class="text-zinc-500 dark:text-zinc-400">Timeout</div>
                        <div class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ $this->currentMonitor->timeout_seconds }} s</div>
                    </div>
                </div>
            </div>
        </flux:card>

        <flux:card class="lg:col-span-2">
            <div class="flex flex-col gap-4">
                <flux:heading size="lg">Critères attendus</flux:heading>

                <div class="flex flex-wrap gap-2">
                    @foreach ($this->currentMonitor->check_criteria ?: Monitor::defaultCheckCriteria() as $criterion)
                        <flux:badge>{{ $this->criterionLabel($criterion) }}</flux:badge>
                    @endforeach
                </div>
            </div>
        </flux:card>
    </div>

    <flux:card class="flex flex-col gap-4">
        <div>
            <flux:heading size="lg">Temps de réponse</flux:heading>
        </div>

        <x-monitor-response-time-sparkline :results="$this->chartCheckResults" description="Temps de réponse et erreurs sur les 150 dernières exécutions" />
    </flux:card>

    <flux:card id="monitor-check-history" class="flex flex-col gap-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <flux:heading size="lg">Historique</flux:heading>

            <flux:checkbox wire:model.live="includeSuccessfulCheckResults" label="Inclure les succès" />
        </div>

        @if ($this->checkResults->isEmpty())
            <flux:text>
                {{ $this->includeSuccessfulCheckResults ? 'Aucune vérification enregistrée pour cette URL.' : 'Aucune erreur enregistrée pour cette URL.' }}
            </flux:text>
        @else
            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Date</flux:table.column>
                        <flux:table.column></flux:table.column>
                        <flux:table.column>Statut</flux:table.column>
                        <flux:table.column>HTTP</flux:table.column>
                        <flux:table.column>Temps</flux:table.column>
                        <flux:table.column>Message</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->checkResults as $checkResult)
                            <flux:table.row :key="$checkResult->id">
                                <flux:table.cell>
                                    <div class="whitespace-nowrap">{{ $checkResult->checked_at->format('d/m/Y H:i') }}</div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <x-relative-time :date="$checkResult->checked_at" class="block text-sm text-zinc-500 dark:text-zinc-400" />
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge :color="$checkResult->status->color()">{{ $checkResult->status->label() }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>{{ $checkResult->http_status ?? '—' }}</flux:table.cell>
                                <flux:table.cell>{{ $checkResult->response_time_ms ? $checkResult->response_time_ms.' ms' : '—' }}</flux:table.cell>
                                <flux:table.cell>
                                    <div class="max-w-xl truncate">{{ $checkResult->error_message ?? $checkResult->response_excerpt ?? '—' }}</div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>

            @if ($this->checkResults->hasPages())
                <flux:pagination :paginator="$this->checkResults" scroll-to="#monitor-check-history" />
            @endif
        @endif
    </flux:card>

    <flux:modal wire:model="showEditMonitorModal" class="md:w-[42rem]">
        <form wire:submit="updateMonitor" class="space-y-6">
            <div>
                <flux:heading size="lg">Modifier l’URL</flux:heading>
                <flux:subheading>{{ $this->selectedMonitor()?->url }}</flux:subheading>
            </div>

            @include('pages.projects.monitor-form-fields')

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Annuler</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">Enregistrer</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
