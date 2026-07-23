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

test('project detail page displays project monitors sorted by name', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(['name' => 'Production']);
    $otherProject = Project::factory()->for($user)->create(['name' => 'Backoffice']);

    Monitor::factory()->for($project)->create([
        'name' => 'Zebra API',
        'url' => 'https://example.com/zebra',
        'current_status' => MonitorStatus::Up,
        'last_checked_at' => now()->subMinutes(2),
        'next_check_at' => now()->addMinutes(4),
    ]);

    $admin = Monitor::factory()->for($project)->create([
        'name' => 'Admin API',
        'url' => 'https://example.com/admin',
        'current_status' => MonitorStatus::Down,
        'last_checked_at' => now()->subMinute(),
        'last_failure_at' => now()->subMinute(),
        'next_check_at' => now()->addMinutes(4),
    ]);

    Monitor::factory()->for($otherProject)->create([
        'name' => 'Billing API',
        'url' => 'https://example.com/billing',
    ]);

    CheckResult::factory()->for($admin)->create([
        'status' => CheckStatus::Up,
        'response_time_ms' => 120,
        'checked_at' => now()->subMinutes(3),
        'checked_url' => $admin->url,
    ]);

    CheckResult::factory()->down()->for($admin)->create([
        'response_time_ms' => 250,
        'checked_at' => now()->subMinute(),
        'checked_url' => $admin->url,
        'error_message' => 'Unexpected HTTP status 500.',
    ]);

    $this->actingAs($user)
        ->get(route('projects.show', $project))
        ->assertOk()
        ->assertSee('Production')
        ->assertDontSee(route('projects.show', $project), false)
        ->assertSeeInOrder(['Admin API', 'Zebra API'])
        ->assertSee('https://example.com/admin')
        ->assertSee('Indisponible')
        ->assertSee('Nom')
        ->assertSee('URL')
        ->assertSee('Dernière exec.')
        ->assertSee('Dernière erreur')
        ->assertSee('Prochaine exec.')
        ->assertSee('Historique des 30 dernières vérifications')
        ->assertSee('120 ms')
        ->assertSee('250 ms')
        ->assertSee('Voir le détail')
        ->assertSee('Vérifier maintenant')
        ->assertSee('openEditMonitorModal', false)
        ->assertSee('openDeleteMonitorModal', false)
        ->assertDontSee('Billing API')
        ->assertDontSee('https://example.com/billing');
});

test('project detail page can update the current project from the edit modal', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(['name' => 'Ancien nom']);

    Livewire::actingAs($user)
        ->test('pages::projects.show', ['project' => $project->id])
        ->assertSee('Modifier')
        ->call('openEditProjectModal', $project->id)
        ->assertSet('showEditProjectModal', true)
        ->assertSet('projectName', 'Ancien nom')
        ->set('projectName', 'Nouveau nom')
        ->call('updateProject')
        ->assertHasNoErrors()
        ->assertSet('showEditProjectModal', false)
        ->assertSee('Nouveau nom');

    expect($project->refresh()->name)->toBe('Nouveau nom');
});

test('project detail page cannot update another user project through the edit action', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->for($user)->create(['name' => 'Projet visible']);
    $otherProject = Project::factory()->for($otherUser)->create(['name' => 'Projet privé']);

    Livewire::actingAs($user)
        ->test('pages::projects.show', ['project' => $project->id])
        ->call('openEditProjectModal', $otherProject->id)
        ->assertSet('showEditProjectModal', false)
        ->set('selectedProjectId', $otherProject->id)
        ->set('projectName', 'Nom volé')
        ->call('updateProject')
        ->assertHasNoErrors();

    expect($otherProject->refresh()->name)->toBe('Projet privé');
});

test('project detail page can add a monitor to the current project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(['name' => 'Production']);

    Livewire::actingAs($user)
        ->test('pages::projects.show', ['project' => $project->id])
        ->assertSee('Ajouter une URL')
        ->call('openAddMonitorModal')
        ->assertSet('showAddMonitorModal', true)
        ->assertSee('Production /')
        ->set('monitorName', 'Status page')
        ->set('url', 'https://status.example.com')
        ->set('intervalMinutes', 15)
        ->set('timeoutSeconds', 8)
        ->call('addMonitor')
        ->assertHasNoErrors()
        ->assertSet('showAddMonitorModal', false)
        ->assertSee('Status page')
        ->assertSee('https://status.example.com');

    $monitor = $project->monitors()->sole();

    expect($monitor->name)->toBe('Status page')
        ->and($monitor->url)->toBe('https://status.example.com')
        ->and($monitor->interval_minutes)->toBe(15)
        ->and($monitor->timeout_seconds)->toBe(8)
        ->and($monitor->check_criteria)->toBe([
            ['type' => MonitorCheckCriterionType::HttpStatus->value, 'expected' => 200],
        ]);
});

test('project detail page can manage a monitor from the actions menu', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));
    Queue::fake();

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'name' => 'Old API',
        'url' => 'https://old.example.com',
        'enabled' => true,
        'interval_minutes' => 15,
        'timeout_seconds' => 10,
        'next_check_at' => now()->addHour(),
        'check_criteria' => [
            ['type' => MonitorCheckCriterionType::HttpStatus->value, 'expected' => 200],
        ],
    ]);

    Livewire::actingAs($user)
        ->test('pages::projects.show', ['project' => $project->id])
        ->assertSee('Vérifier maintenant')
        ->call('checkNow', $monitor->id)
        ->assertHasNoErrors()
        ->call('openEditMonitorModal', $monitor->id)
        ->assertSet('showEditMonitorModal', true)
        ->assertSet('monitorName', 'Old API')
        ->assertSet('url', 'https://old.example.com')
        ->assertSee($project->name.' /')
        ->set('monitorName', 'New API')
        ->set('url', 'https://new.example.com')
        ->set('enabled', false)
        ->set('intervalMinutes', 30)
        ->set('timeoutSeconds', 8)
        ->set('checkCriteria', [
            ['type' => MonitorCheckCriterionType::HttpStatus->value, 'expected' => 204],
            ['type' => MonitorCheckCriterionType::JsonPath->value, 'path' => 'status', 'expected' => 'ok'],
        ])
        ->call('updateMonitor')
        ->assertHasNoErrors()
        ->assertSet('showEditMonitorModal', false)
        ->assertSee('New API')
        ->assertSee('https://new.example.com')
        ->call('openDeleteMonitorModal', $monitor->id)
        ->assertSet('showDeleteMonitorModal', true)
        ->call('deleteMonitor')
        ->assertSet('showDeleteMonitorModal', false);

    Queue::assertPushed(CheckMonitorJob::class, fn (CheckMonitorJob $job): bool => $job->monitorId === $monitor->id);

    expect($monitor->fresh())->toBeNull();
});

test('project detail page cannot manage another user monitor', function () {
    Queue::fake();

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $otherUser = User::factory()->create();
    $otherProject = Project::factory()->for($otherUser)->create();
    $otherMonitor = Monitor::factory()->for($otherProject)->create([
        'name' => 'Private API',
    ]);

    Livewire::actingAs($user)
        ->test('pages::projects.show', ['project' => $project->id])
        ->call('checkNow', $otherMonitor->id)
        ->assertHasNoErrors()
        ->call('openEditMonitorModal', $otherMonitor->id)
        ->assertSet('showEditMonitorModal', false)
        ->set('selectedMonitorId', $otherMonitor->id)
        ->set('monitorName', 'Stolen API')
        ->set('url', 'https://stolen.example.com')
        ->call('updateMonitor')
        ->assertHasNoErrors()
        ->call('openDeleteMonitorModal', $otherMonitor->id)
        ->assertSet('showDeleteMonitorModal', false)
        ->call('deleteMonitor')
        ->assertHasNoErrors();

    Queue::assertNotPushed(CheckMonitorJob::class);

    expect($otherMonitor->refresh()->name)->toBe('Private API');
});

test('project detail page displays an overdue next check as now', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(['name' => 'Production']);
    Monitor::factory()->for($project)->create([
        'name' => 'API',
        'current_status' => MonitorStatus::Up,
        'last_checked_at' => now()->subMinutes(6),
        'next_check_at' => now()->subMinute(),
    ]);

    $this->actingAs($user)
        ->get(route('projects.show', $project))
        ->assertOk()
        ->assertSee('il y a 6 minutes')
        ->assertSee('maintenant')
        ->assertSee('data-relative-time-mode="due"', false)
        ->assertSee('data-relative-time=', false);
});

test('project detail mini chart only displays the latest thirty check results per monitor', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'name' => 'API',
    ]);

    CheckResult::factory()->for($monitor)->create([
        'status' => CheckStatus::Up,
        'response_time_ms' => 999,
        'checked_at' => now()->subMinutes(31),
        'checked_url' => $monitor->url,
    ]);

    foreach (range(1, 30) as $minute) {
        CheckResult::factory()->for($monitor)->create([
            'status' => CheckStatus::Up,
            'response_time_ms' => 100 + $minute,
            'checked_at' => now()->subMinutes($minute),
            'checked_url' => $monitor->url,
        ]);
    }

    $this->actingAs($user)
        ->get(route('projects.show', $project))
        ->assertOk()
        ->assertSee('130 ms')
        ->assertDontSee('999 ms');
});

test('project detail page refreshes monitor rows when a monitor check completed event is received', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(['name' => 'Production']);
    $monitor = Monitor::factory()->for($project)->create([
        'current_status' => MonitorStatus::Up,
        'last_checked_at' => now()->subMinutes(10),
        'next_check_at' => now()->subMinute(),
    ]);

    $component = Livewire::actingAs($user)
        ->test('pages::projects.show', ['project' => $project->id])
        ->assertSee('OK')
        ->assertSee('maintenant');

    CheckResult::factory()->down()->for($monitor)->create([
        'response_time_ms' => 300,
        'checked_at' => now(),
        'checked_url' => $monitor->url,
    ]);

    $monitor->forceFill([
        'current_status' => MonitorStatus::Down,
        'last_checked_at' => now(),
        'last_failure_at' => now(),
        'next_check_at' => now()->addMinutes(15),
    ])->save();

    $component
        ->call('refreshAfterMonitorCheckCompleted')
        ->assertSee('Indisponible')
        ->assertSee('300 ms')
        ->assertSee('dans 15 minutes');
});

test('project detail page is not available for another user project', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->for($otherUser)->create();

    $this->actingAs($user)
        ->get(route('projects.show', $project))
        ->assertNotFound();
});
