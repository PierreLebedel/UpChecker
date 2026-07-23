<?php

use App\Enums\CheckStatus;
use App\Enums\MonitorStatus;
use App\Models\CheckResult;
use App\Models\Monitor;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

afterEach(function () {
    Cache::flush();
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

test('dashboard displays recent failure cards above monitored urls', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(['name' => 'Production']);
    $monitor = Monitor::factory()->for($project)->create([
        'name' => 'API publique',
        'url' => 'https://example.com/health',
        'current_status' => MonitorStatus::Down,
        'last_failure_at' => now()->subMinutes(20),
    ]);
    $oldMonitor = Monitor::factory()->for($project)->create([
        'name' => 'Ancienne erreur',
    ]);
    $otherUser = User::factory()->create();
    $otherProject = Project::factory()->for($otherUser)->create();
    $otherMonitor = Monitor::factory()->for($otherProject)->create([
        'name' => 'Erreur privée',
    ]);

    CheckResult::factory()->down()->for($monitor)->create([
        'checked_at' => now()->subMinutes(20),
        'checked_url' => $monitor->url,
        'error_message' => 'Unexpected HTTP status 500.',
    ]);
    CheckResult::factory()->down()->for($monitor)->create([
        'checked_at' => now()->subHours(2),
        'checked_url' => $monitor->url,
        'error_message' => 'Timeout marker.',
    ]);
    CheckResult::factory()->down()->for($oldMonitor)->create([
        'checked_at' => now()->subDay()->subMinute(),
        'checked_url' => $oldMonitor->url,
        'error_message' => 'Old failure marker.',
    ]);
    CheckResult::factory()->down()->for($otherMonitor)->create([
        'checked_at' => now()->subMinute(),
        'checked_url' => $otherMonitor->url,
        'error_message' => 'Private failure marker.',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Erreurs récentes', 'Dernière exec.'])
        ->assertSee('API publique')
        ->assertSee('https://example.com/health')
        ->assertSee('Production')
        ->assertSee('2 erreurs')
        ->assertSee('Unexpected HTTP status 500.')
        ->assertDontSee('Old failure marker.')
        ->assertDontSee('Private failure marker.');
});

test('dashboard hides recent failure section when there are no failures in the last day', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create();

    CheckResult::factory()->for($monitor)->create([
        'status' => CheckStatus::Up,
        'checked_at' => now()->subMinute(),
        'checked_url' => $monitor->url,
    ]);
    CheckResult::factory()->down()->for($monitor)->create([
        'checked_at' => now()->subDay()->subMinute(),
        'checked_url' => $monitor->url,
        'error_message' => 'Old failure marker.',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Erreurs récentes')
        ->assertDontSee('Old failure marker.');
});

test('dashboard can forget recent failures until a newer failure is recorded', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(['name' => 'Production']);
    $monitor = Monitor::factory()->for($project)->create([
        'name' => 'API publique',
        'url' => 'https://example.com/health',
        'current_status' => MonitorStatus::Down,
        'last_failure_at' => now()->subMinutes(5),
    ]);

    CheckResult::factory()->down()->for($monitor)->create([
        'checked_at' => now()->subMinutes(5),
        'checked_url' => $monitor->url,
        'error_message' => 'Initial failure marker.',
    ]);
    CheckResult::factory()->down()->for($monitor)->create([
        'checked_at' => now()->subHours(2),
        'checked_url' => $monitor->url,
        'error_message' => 'Older failure marker.',
    ]);

    $component = Livewire\Livewire::actingAs($user)
        ->test('pages::dashboard')
        ->assertSee('Erreurs récentes')
        ->assertSee('2 erreurs')
        ->assertSee('Initial failure marker.');

    $component
        ->call('forgetRecentFailures')
        ->assertDontSee('Erreurs récentes')
        ->assertDontSee('Initial failure marker.');

    expect(Cache::has("users:{$user->id}:dashboard:recent-failures-forgotten-at"))->toBeTrue();

    Carbon::setTestNow(Carbon::parse('2026-07-16 12:02:00'));

    CheckResult::factory()->down()->for($monitor)->create([
        'response_time_ms' => 320,
        'checked_at' => now(),
        'checked_url' => $monitor->url,
        'error_message' => 'New failure marker.',
    ]);

    $monitor->forceFill([
        'last_failure_at' => now(),
    ])->save();

    $component
        ->call('refreshAfterMonitorCheckCompleted')
        ->assertSee('Erreurs récentes')
        ->assertSee('1 erreur')
        ->assertSee('New failure marker.')
        ->assertDontSee('Initial failure marker.')
        ->assertDontSee('Older failure marker.');
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

test('dashboard mini chart pads missing check results with gray placeholders', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'name' => 'API',
    ]);

    foreach (range(1, 2) as $minute) {
        CheckResult::factory()->for($monitor)->create([
            'status' => CheckStatus::Up,
            'response_time_ms' => 100 + $minute,
            'checked_at' => now()->subMinutes($minute),
            'checked_url' => $monitor->url,
        ]);
    }

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertSee('Historique des 30 dernières vérifications')
        ->assertSee('102 ms');

    expect(substr_count($response->getContent(), 'data-monitor-check-placeholder'))->toBe(28);
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
        ->assertSee('Erreurs récentes')
        ->assertSee('Indisponible')
        ->assertSee('1 erreur')
        ->assertSee('300 ms')
        ->assertSee('dans 15 minutes');
});
