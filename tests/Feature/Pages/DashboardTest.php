<?php

use App\Enums\CheckStatus;
use App\Enums\MonitorStatus;
use App\Models\CheckResult;
use App\Models\Monitor;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

test('dashboard displays monitor cards sorted by name', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));

    $user = User::factory()->create();
    $production = Project::factory()->for($user)->create(['name' => 'Production']);
    $backoffice = Project::factory()->for($user)->create(['name' => 'Backoffice']);

    Monitor::factory()->for($production)->create([
        'name' => 'Zebra API',
        'url' => 'https://example.com/zebra',
        'current_status' => MonitorStatus::Up,
        'last_checked_at' => now()->subMinutes(2),
        'next_check_at' => now()->addMinutes(4),
    ]);

    $admin = Monitor::factory()->for($backoffice)->create([
        'name' => 'Admin API',
        'url' => 'https://example.com/admin',
        'current_status' => MonitorStatus::Down,
        'last_checked_at' => now()->subMinute(),
        'last_failure_at' => now()->subMinute(),
        'next_check_at' => now()->addMinutes(4),
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
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Admin API', 'Zebra API'])
        ->assertSee('Backoffice')
        ->assertSee('Production')
        ->assertSee(route('projects.show', $backoffice), false)
        ->assertSee(route('projects.show', $production), false)
        ->assertSee('https://example.com/admin')
        ->assertSee('Indisponible')
        ->assertSee('Dernière exec.')
        ->assertSee('Dernière erreur')
        ->assertSee('Prochaine exec.')
        ->assertSee('Historique des 30 dernières vérifications')
        ->assertSee('120 ms')
        ->assertSee('250 ms');
});

test('dashboard displays an overdue next check as now', function () {
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
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('il y a 6 minutes')
        ->assertSee('maintenant')
        ->assertSee('data-relative-time-mode="due"', false)
        ->assertSee('data-relative-time=', false);
});

test('dashboard mini chart only displays the latest thirty check results', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'name' => 'API',
    ]);
    $otherMonitor = Monitor::factory()->for($project)->create([
        'name' => 'Worker',
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

    foreach (range(1, 30) as $minute) {
        CheckResult::factory()->for($otherMonitor)->create([
            'status' => CheckStatus::Up,
            'response_time_ms' => 200 + $minute,
            'checked_at' => now()->subMinutes($minute),
            'checked_url' => $otherMonitor->url,
        ]);
    }

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('130 ms')
        ->assertSee('230 ms')
        ->assertDontSee('999 ms');
});

test('dashboard refreshes monitor cards when a monitor check completed event is received', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(['name' => 'Production']);
    $monitor = Monitor::factory()->for($project)->create([
        'current_status' => MonitorStatus::Up,
        'last_checked_at' => now()->subMinutes(10),
        'next_check_at' => now()->subMinute(),
    ]);

    $component = Livewire\Livewire::actingAs($user)
        ->test('pages::dashboard')
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
