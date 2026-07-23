<?php

use App\Enums\MonitorCheckCriterionType;
use App\Jobs\CheckMonitorJob;
use App\Models\Monitor;
use App\Models\Project;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Projet')] class extends Component
{
    public string $project;

    public bool $showEditProjectModal = false;

    public bool $showAddMonitorModal = false;

    public bool $showEditMonitorModal = false;

    public bool $showDeleteMonitorModal = false;

    public ?string $selectedProjectId = null;

    public ?string $selectedMonitorId = null;

    public string $projectName = '';

    public string $monitorName = '';

    public string $url = '';

    public bool $enabled = true;

    public int $intervalMinutes = 5;

    public int $timeoutSeconds = 10;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $checkCriteria = [];

    public function mount(string $project): void
    {
        $this->project = $project;
    }

    /**
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        return [
            'echo-private:users.'.Auth::id().',MonitorCheckCompleted' => 'refreshAfterMonitorCheckCompleted',
        ];
    }

    #[Computed]
    public function currentProject(): Project
    {
        return Auth::user()
            ->projects()
            ->whereKey($this->project)
            ->firstOrFail();
    }

    /**
     * @return Collection<int, Monitor>
     */
    #[Computed]
    public function monitors(): Collection
    {
        return Monitor::query()
            ->select([
                'id',
                'project_id',
                'name',
                'url',
                'enabled',
                'current_status',
                'last_checked_at',
                'last_failure_at',
                'next_check_at',
            ])
            ->whereBelongsTo($this->currentProject, 'project')
            ->with([
                'project:id,user_id,name',
                'checkResults' => fn ($query) => $query
                    ->select([
                        'id',
                        'monitor_id',
                        'status',
                        'response_time_ms',
                        'checked_at',
                    ])
                    ->latest('checked_at')
                    ->limit(30),
            ])
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<string, string>  $event
     */
    public function refreshAfterMonitorCheckCompleted(array $event = []): void
    {
        unset($this->monitors);
    }

    public function openEditProjectModal(string $projectId): void
    {
        $project = $this->ownedProject($projectId);

        if (! $project instanceof Project) {
            return;
        }

        $this->selectedProjectId = $project->id;
        $this->projectName = $project->name;
        $this->resetValidation();
        $this->showEditProjectModal = true;
    }

    public function updateProject(): void
    {
        $project = $this->ownedProject($this->selectedProjectId);

        if (! $project instanceof Project) {
            return;
        }

        $validated = $this->validate([
            'projectName' => ['required', 'string', 'max:255'],
        ]);

        $project->update([
            'name' => $validated['projectName'],
        ]);

        unset($this->currentProject, $this->monitors);

        $this->showEditProjectModal = false;
        $this->selectedProjectId = null;
        $this->resetProjectForm();

        Flux::toast(variant: 'success', text: 'Projet modifié.');
    }

    public function openAddMonitorModal(): void
    {
        $this->resetMonitorForm();
        $this->showAddMonitorModal = true;
    }

    public function addMonitor(): void
    {
        $this->validate($this->monitorRules());
        $this->createMonitorForProject($this->currentProject);

        unset($this->monitors);

        $this->showAddMonitorModal = false;
        $this->resetMonitorForm();

        Flux::toast(variant: 'success', text: 'URL ajoutée.');
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

        unset($this->monitors);

        $this->showEditMonitorModal = false;
        $this->resetMonitorForm();

        Flux::toast(variant: 'success', text: 'URL modifiée.');
    }

    public function openDeleteMonitorModal(string $monitorId): void
    {
        $monitor = $this->ownedMonitor($monitorId);

        if (! $monitor instanceof Monitor) {
            return;
        }

        $this->selectedMonitorId = $monitor->id;
        $this->showDeleteMonitorModal = true;
    }

    public function deleteMonitor(): void
    {
        $monitor = $this->ownedMonitor($this->selectedMonitorId);

        if (! $monitor instanceof Monitor) {
            return;
        }

        $monitor->delete();

        unset($this->monitors);

        $this->showDeleteMonitorModal = false;
        $this->selectedMonitorId = null;

        Flux::toast(variant: 'success', text: 'URL supprimée.');
    }

    public function checkNow(string $monitorId): void
    {
        $monitor = $this->ownedMonitor($monitorId);

        if (! $monitor instanceof Monitor) {
            return;
        }

        $monitor->forceFill([
            'next_check_at' => now()->addMinutes($monitor->interval_minutes),
        ])->save();

        CheckMonitorJob::dispatch($monitor->id);

        unset($this->monitors);

        Flux::toast(variant: 'success', text: 'Vérification lancée.');
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

    private function createMonitorForProject(Project $project): Monitor
    {
        return $project->monitors()->create($this->monitorAttributes());
    }

    private function ownedProject(?string $projectId): ?Project
    {
        if ($projectId === null) {
            return null;
        }

        return Auth::user()
            ->projects()
            ->whereKey($projectId)
            ->first();
    }

    private function ownedMonitor(?string $monitorId): ?Monitor
    {
        if ($monitorId === null) {
            return null;
        }

        return Monitor::query()
            ->whereKey($monitorId)
            ->whereBelongsTo($this->currentProject, 'project')
            ->first();
    }

    private function resetProjectForm(): void
    {
        $this->projectName = '';
        $this->resetValidation();
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
        <flux:breadcrumbs.item :href="route('dashboard')" separator="slash">Dashboard</flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="route('projects.index')" separator="slash">Projets</flux:breadcrumbs.item>
        <flux:breadcrumbs.item separator="slash">{{ $this->currentProject->name }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ $this->currentProject->name }}</flux:heading>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:button icon="plus" wire:click="openAddMonitorModal">
                Ajouter une URL
            </flux:button>

            <flux:button icon="pencil-square" wire:click="openEditProjectModal('{{ $this->project }}')">
                Modifier
            </flux:button>

            <flux:button icon="arrow-left" :href="route('projects.index')" wire:navigate>
                Retour aux projets
            </flux:button>
        </div>
    </div>

    @if ($this->monitors->isEmpty())
        <flux:card class="max-w-2xl">
            <div class="flex flex-col gap-4">
                <div>
                    <flux:heading>Aucune URL surveillée</flux:heading>
                    <flux:text class="mt-1">Ajoutez une URL depuis la page Projets pour démarrer la surveillance de ce projet.</flux:text>
                </div>

                <div>
                    <flux:button variant="primary" icon="plus" :href="route('projects.index')" wire:navigate>
                        Gérer les projets
                    </flux:button>
                </div>
            </div>
        </flux:card>
    @else
        <flux:card class="overflow-hidden py-3">
            <x-monitors-status-table :monitors="$this->monitors" :show-project-column="false" />
        </flux:card>
    @endif

    <flux:modal wire:model="showEditProjectModal" class="max-w-lg">
        <form wire:submit="updateProject" class="space-y-6">
            <div>
                <flux:heading size="lg">Modifier le projet</flux:heading>
                <flux:subheading>Renommez le projet sans modifier ses URL surveillées.</flux:subheading>
            </div>

            <flux:input wire:model="projectName" label="Nom du projet" required autofocus />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Annuler</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">Enregistrer</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal wire:model="showAddMonitorModal" class="md:w-[42rem]">
        <form wire:submit="addMonitor" class="space-y-6">
            <div>
                <flux:heading size="lg">Ajouter une URL</flux:heading>
                <flux:subheading>{{ $this->currentProject->name }}</flux:subheading>
            </div>

            @include('pages.projects.monitor-form-fields')

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Annuler</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">Ajouter</flux:button>
            </div>
        </form>
    </flux:modal>

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

    <flux:modal wire:model="showDeleteMonitorModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Supprimer cette URL ?</flux:heading>
                <flux:subheading>{{ $this->selectedMonitor()?->url }}</flux:subheading>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Annuler</flux:button>
                </flux:modal.close>
                <flux:button variant="subtle" class="text-rose-600 hover:bg-rose-50 hover:text-rose-700 dark:text-rose-400 dark:hover:bg-rose-950/40 dark:hover:text-rose-300" wire:click="deleteMonitor">Supprimer</flux:button>
            </div>
        </div>
    </flux:modal>
</section>
