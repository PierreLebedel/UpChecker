@php
    $httpStatusCriterion = \App\Enums\MonitorCheckCriterionType::HttpStatus;
    $jsonPathCriterion = \App\Enums\MonitorCheckCriterionType::JsonPath;
    $bodyContainsCriterion = \App\Enums\MonitorCheckCriterionType::BodyContains;
    $projectNamePrefix = trim((string) ($projectNamePrefix ?? ''));
    $projectNamePrefix = $projectNamePrefix === '' ? 'Projet' : $projectNamePrefix;
@endphp

<div class="grid gap-5">
    <div class="grid gap-4 md:grid-cols-2">
        <flux:field>
            <flux:label>Nom du contrôle</flux:label>
            <flux:input.group>
                <flux:input.group.prefix>{{ $projectNamePrefix }} /</flux:input.group.prefix>
                <flux:input wire:model="monitorName" required />
            </flux:input.group>
        </flux:field>
        <flux:input wire:model="url" label="URL" type="url" placeholder="https://example.com/health" required />
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <flux:select wire:model="intervalMinutes" label="Intervalle">
            <option value="1">1 minute</option>
            <option value="2">2 minutes</option>
            <option value="5">5 minutes</option>
            <option value="15">15 minutes</option>
            <option value="30">30 minutes</option>
            <option value="60">60 minutes</option>
        </flux:select>

        <flux:input wire:model="timeoutSeconds" label="Timeout" type="number" min="1" max="60" />

        <div class="pt-9">
            <flux:checkbox wire:model="enabled" label="Contrôle actif" />
        </div>
    </div>

    <flux:separator />

    <div class="grid gap-3">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="sm">Critères de vérification</flux:heading>
                <flux:text class="mt-1">Ajoutez au moins un critère. Le code HTTP ne peut apparaître qu’une seule fois.</flux:text>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:button size="sm" icon="plus" wire:click="addCriterion('{{ $httpStatusCriterion->value }}')" :disabled="$this->hasHttpStatusCriterion()">
                Code HTTP
            </flux:button>
            <flux:button size="sm" icon="plus" wire:click="addCriterion('{{ $jsonPathCriterion->value }}')">
                Champ JSON
            </flux:button>
            <flux:button size="sm" icon="plus" wire:click="addCriterion('{{ $bodyContainsCriterion->value }}')">
                Texte réponse
            </flux:button>
        </div>

        <flux:error name="checkCriteria" />

        <div class="grid gap-3">
            @foreach ($checkCriteria as $index => $criterion)
                @php
                    $criterionType = \App\Enums\MonitorCheckCriterionType::tryFrom((string) ($criterion['type'] ?? ''));
                @endphp

                <div wire:key="monitor-criterion-{{ $index }}" class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <flux:badge>{{ $criterionType?->label() ?? 'Critère' }}</flux:badge>
                        </div>

                        <flux:button size="xs" variant="subtle" icon="trash" class="text-rose-600 hover:bg-rose-50 hover:text-rose-700 dark:text-rose-400 dark:hover:bg-rose-950/40 dark:hover:text-rose-300" wire:click="removeCriterion({{ $index }})">
                            Retirer
                        </flux:button>
                    </div>

                    <div class="mt-3">
                        @if (($criterion['type'] ?? null) === $httpStatusCriterion->value)
                            <flux:input wire:model="checkCriteria.{{ $index }}.expected" label="Code HTTP attendu" type="number" min="100" max="599" />
                            <flux:error name="checkCriteria.{{ $index }}.expected" />
                        @elseif (($criterion['type'] ?? null) === $jsonPathCriterion->value)
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <flux:input wire:model="checkCriteria.{{ $index }}.path" label="Champ JSON attendu" placeholder="status" />
                                    <flux:error name="checkCriteria.{{ $index }}.path" />
                                </div>
                                <div>
                                    <flux:input wire:model="checkCriteria.{{ $index }}.expected" label="Valeur attendue" placeholder="ok" />
                                    <flux:error name="checkCriteria.{{ $index }}.expected" />
                                </div>
                            </div>
                        @elseif (($criterion['type'] ?? null) === $bodyContainsCriterion->value)
                            <flux:textarea wire:model="checkCriteria.{{ $index }}.text" label="Texte attendu dans la réponse" rows="3" />
                            <flux:error name="checkCriteria.{{ $index }}.text" />
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
