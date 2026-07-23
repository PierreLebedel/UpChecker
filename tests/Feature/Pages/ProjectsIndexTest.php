<?php

use App\Enums\CheckStatus;
use App\Enums\MonitorCheckCriterionType;
use App\Enums\MonitorStatus;
use App\Jobs\CheckMonitorJob;
use App\Models\CheckResult;
use App\Models\Monitor;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

afterEach(function () {
    Carbon::setTestNow();
});

test('projects page lists project cards alphabetically with their monitor status tables', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(['name' => 'Production']);
    $backoffice = Project::factory()->for($user)->create(['name' => 'Backoffice']);
    Monitor::factory()->for($project)->create([
        'name' => 'API publique',
        'url' => 'https://example.com/health',
        'current_status' => MonitorStatus::Up,
    ]);
    $admin = Monitor::factory()->for($backoffice)->create([
        'name' => 'Admin',
        'url' => 'https://admin.example.com/health',
        'current_status' => MonitorStatus::Down,
        'last_failure_at' => now()->subMinute(),
    ]);
    CheckResult::factory()->for($admin)->create([
        'status' => CheckStatus::Up,
        'response_time_ms' => 120,
        'checked_at' => now()->subMinutes(2),
        'checked_url' => 'https://admin.example.com/health',
    ]);

    $this->actingAs($user)
        ->get(route('projects.index'))
        ->assertOk()
        ->assertSeeInOrder(['Backoffice', 'Production'])
        ->assertSee(route('projects.show', $backoffice), false)
        ->assertSee(route('projects.show', $project), false)
        ->assertSee('Production')
        ->assertSee('API publique')
        ->assertSee('https://example.com/health')
        ->assertSee('Admin')
        ->assertSee('https://admin.example.com/health')
        ->assertSee('OK')
        ->assertSee('Indisponible')
        ->assertSee('Historique')
        ->assertSee('Dernière exec.')
        ->assertSee('Dernière erreur')
        ->assertSee('Prochaine exec.')
        ->assertSee('Historique des 30 dernières vérifications')
        ->assertSee('Voir le détail')
        ->assertSee('Vérifier maintenant')
        ->assertSee('Modifier')
        ->assertSee('Supprimer');
});

test('projects page displays an overdue next check as now', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(['name' => 'Production']);
    Monitor::factory()->for($project)->create([
        'name' => 'API publique',
        'url' => 'https://example.com/health',
        'current_status' => MonitorStatus::Up,
        'last_checked_at' => now()->subMinutes(6),
        'next_check_at' => now()->subMinute(),
    ]);

    $this->actingAs($user)
        ->get(route('projects.index'))
        ->assertOk()
        ->assertSee('il y a 6 minutes')
        ->assertSee('maintenant')
        ->assertSee('data-relative-time-mode="due"', false)
        ->assertSee('data-relative-time=', false);
});

test('projects page refreshes monitor rows when a monitor check completed event is received', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(['name' => 'Production']);
    $monitor = Monitor::factory()->for($project)->create([
        'name' => 'API publique',
        'current_status' => MonitorStatus::Up,
        'last_checked_at' => now()->subMinutes(10),
        'next_check_at' => now()->subMinute(),
    ]);

    $component = Livewire::actingAs($user)
        ->test('pages::projects.index')
        ->assertSee('OK')
        ->assertSee('maintenant');

    $monitor->forceFill([
        'current_status' => MonitorStatus::Down,
        'last_checked_at' => now(),
        'next_check_at' => now()->addMinutes(15),
    ])->save();

    $component
        ->call('refreshAfterMonitorCheckCompleted')
        ->assertSee('Indisponible')
        ->assertSee('dans 15 minutes');
});

test('a project can be created with its first monitor', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::projects.index')
        ->call('openCreateProjectModal')
        ->set('projectName', 'Production')
        ->assertSee('Production /')
        ->set('monitorName', 'API')
        ->set('url', 'https://example.com/health')
        ->set('intervalMinutes', 15)
        ->set('timeoutSeconds', 8)
        ->call('createProject')
        ->assertHasNoErrors()
        ->assertSet('showCreateProjectModal', false);

    $project = Project::query()->whereBelongsTo($user)->where('name', 'Production')->first();

    expect($project)->not->toBeNull()
        ->and($project->monitors)->toHaveCount(1)
        ->and($project->monitors->first()->name)->toBe('API')
        ->and($project->monitors->first()->interval_minutes)->toBe(15)
        ->and($project->monitors->first()->check_criteria)->toBe([
            ['type' => MonitorCheckCriterionType::HttpStatus->value, 'expected' => 200],
        ]);
});

test('a project can be updated', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(['name' => 'Ancien nom']);

    Livewire::actingAs($user)
        ->test('pages::projects.index')
        ->call('openEditProjectModal', $project->id)
        ->assertSet('showEditProjectModal', true)
        ->assertSet('projectName', 'Ancien nom')
        ->set('projectName', 'Nouveau nom')
        ->call('updateProject')
        ->assertHasNoErrors()
        ->assertSet('showEditProjectModal', false);

    expect($project->refresh()->name)->toBe('Nouveau nom');
});

test('a user cannot update another user project', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->for($otherUser)->create(['name' => 'Projet privé']);

    Livewire::actingAs($user)
        ->test('pages::projects.index')
        ->call('openEditProjectModal', $project->id)
        ->assertSet('showEditProjectModal', false)
        ->set('selectedProjectId', $project->id)
        ->set('projectName', 'Nom volé')
        ->call('updateProject')
        ->assertHasNoErrors();

    expect($project->refresh()->name)->toBe('Projet privé');
});

test('a monitor can be added to an existing project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::projects.index')
        ->call('openAddMonitorModal', $project->id)
        ->assertSee($project->name.' /')
        ->set('monitorName', 'Status page')
        ->set('url', 'https://status.example.com')
        ->call('addMonitor')
        ->assertHasNoErrors()
        ->assertSet('showAddMonitorModal', false);

    expect($project->monitors()->where('name', 'Status page')->exists())->toBeTrue();
});

test('a monitor can be updated', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'name' => 'Old API',
        'url' => 'https://old.example.com',
    ]);

    Livewire::actingAs($user)
        ->test('pages::projects.index')
        ->call('openEditMonitorModal', $monitor->id)
        ->assertSee($project->name.' /')
        ->set('monitorName', 'New API')
        ->set('url', 'https://new.example.com')
        ->set('enabled', false)
        ->set('checkCriteria', [
            ['type' => MonitorCheckCriterionType::HttpStatus->value, 'expected' => 204],
            ['type' => MonitorCheckCriterionType::JsonPath->value, 'path' => 'status', 'expected' => 'ok'],
            ['type' => MonitorCheckCriterionType::BodyContains->value, 'text' => 'ready'],
        ])
        ->call('updateMonitor')
        ->assertHasNoErrors()
        ->assertSet('showEditMonitorModal', false);

    $monitor->refresh();

    expect($monitor->name)->toBe('New API')
        ->and($monitor->url)->toBe('https://new.example.com')
        ->and($monitor->enabled)->toBeFalse()
        ->and($monitor->check_criteria)->toBe([
            ['type' => MonitorCheckCriterionType::HttpStatus->value, 'expected' => 204],
            ['type' => MonitorCheckCriterionType::JsonPath->value, 'path' => 'status', 'expected' => 'ok'],
            ['type' => MonitorCheckCriterionType::BodyContains->value, 'text' => 'ready'],
        ]);
});

test('a monitor requires at least one check criterion', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::projects.index')
        ->call('openAddMonitorModal', $project->id)
        ->set('monitorName', 'Status page')
        ->set('url', 'https://status.example.com')
        ->set('checkCriteria', [])
        ->call('addMonitor')
        ->assertHasErrors(['checkCriteria']);
});

test('a monitor cannot define the http status criterion twice', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test('pages::projects.index')
        ->call('openAddMonitorModal', $project->id)
        ->set('monitorName', 'Status page')
        ->set('url', 'https://status.example.com')
        ->set('checkCriteria', [
            ['type' => MonitorCheckCriterionType::HttpStatus->value, 'expected' => 200],
            ['type' => MonitorCheckCriterionType::HttpStatus->value, 'expected' => 204],
        ])
        ->call('addMonitor')
        ->assertHasErrors(['checkCriteria']);
});

test('a monitor check can be dispatched manually', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));
    Queue::fake();

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'interval_minutes' => 15,
        'next_check_at' => now()->addHour(),
    ]);

    Livewire::actingAs($user)
        ->test('pages::projects.index')
        ->call('checkNow', $monitor->id)
        ->assertHasNoErrors();

    Queue::assertPushed(CheckMonitorJob::class, fn (CheckMonitorJob $job): bool => $job->monitorId === $monitor->id);

    expect($monitor->refresh()->next_check_at->equalTo(now()->addMinutes(15)))->toBeTrue();
});

test('a user cannot manually dispatch a check for another user monitor', function () {
    Queue::fake();

    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->for($otherUser)->create();
    $monitor = Monitor::factory()->for($project)->create();

    Livewire::actingAs($user)
        ->test('pages::projects.index')
        ->call('checkNow', $monitor->id)
        ->assertHasNoErrors();

    Queue::assertNotPushed(CheckMonitorJob::class);
});

test('a monitor can be deleted after confirmation', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create();

    Livewire::actingAs($user)
        ->test('pages::projects.index')
        ->call('openDeleteMonitorModal', $monitor->id)
        ->assertSet('showDeleteMonitorModal', true)
        ->call('deleteMonitor')
        ->assertSet('showDeleteMonitorModal', false);

    expect($monitor->fresh())->toBeNull();
});

test('project deletion confirmation includes monitor count', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(['name' => 'Production']);
    Monitor::factory()->count(3)->for($project)->create();

    Livewire::actingAs($user)
        ->test('pages::projects.index')
        ->call('openDeleteProjectModal', $project->id)
        ->assertSet('showDeleteProjectModal', true)
        ->assertSee('ses 3 contrôles')
        ->call('deleteProject')
        ->assertSet('showDeleteProjectModal', false);

    expect($project->fresh())->toBeNull();
});
