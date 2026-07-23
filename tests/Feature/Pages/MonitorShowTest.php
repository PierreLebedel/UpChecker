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

test('monitor detail page displays current status and recent check history', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(['name' => 'Production']);
    $monitor = Monitor::factory()->for($project)->create([
        'name' => 'API publique',
        'url' => 'https://example.com/health',
        'current_status' => MonitorStatus::Down,
        'last_checked_at' => now()->subMinutes(6),
        'last_failure_at' => now()->subMinutes(6),
        'next_check_at' => now()->addMinutes(9),
        'interval_minutes' => 15,
        'timeout_seconds' => 8,
        'check_criteria' => [
            ['type' => MonitorCheckCriterionType::HttpStatus->value, 'expected' => 200],
            ['type' => MonitorCheckCriterionType::JsonPath->value, 'path' => 'status', 'expected' => 'ok'],
            ['type' => MonitorCheckCriterionType::BodyContains->value, 'text' => 'ready'],
        ],
    ]);

    CheckResult::factory()->for($monitor)->create([
        'status' => CheckStatus::Up,
        'http_status' => 200,
        'response_time_ms' => 120,
        'checked_url' => 'https://example.com/health',
        'checked_at' => now()->subMinutes(20),
        'response_excerpt' => '{"status":"ok"}',
    ]);

    CheckResult::factory()->down()->for($monitor)->create([
        'checked_url' => 'https://example.com/health',
        'checked_at' => now()->subMinutes(6),
        'response_time_ms' => 250,
        'error_message' => 'Unexpected HTTP status 500.',
    ]);

    $this->actingAs($user)
        ->get(route('monitors.show', $monitor))
        ->assertOk()
        ->assertSee('API publique')
        ->assertSee('https://example.com/health')
        ->assertSee('Production')
        ->assertSee(route('projects.show', $project), false)
        ->assertSee('Indisponible')
        ->assertSee('il y a 6 minutes')
        ->assertSee('dans 9 minutes')
        ->assertSee('15 min')
        ->assertSee('8 s')
        ->assertSee('HTTP 200')
        ->assertSee('status = ok')
        ->assertSee('Contient')
        ->assertSee('data-monitor-response-time-sparkline', false)
        ->assertSee('Temps de réponse et erreurs sur les 150 dernières exécutions')
        ->assertSee('16/07/2026 11:40:00 - OK - 120 ms - HTTP 200', false)
        ->assertSee('16/07/2026 11:54:00 - Indisponible - 250 ms - HTTP 500 - Unexpected HTTP status 500.', false)
        ->assertSee('data-relative-time=', false)
        ->assertSee('Temps de réponse')
        ->assertSee('Historique')
        ->assertSee('Unexpected HTTP status 500.')
        ->assertSee('120 ms')
        ->assertSeeInOrder(['Unexpected HTTP status 500.', '{"status":"ok"}']);
});

test('monitor detail page can dispatch an immediate monitor check', function () {
    Queue::fake();
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'interval_minutes' => 15,
        'next_check_at' => now()->addHour(),
    ]);

    Livewire::actingAs($user)
        ->test('pages::monitors.show', ['monitor' => $monitor->id])
        ->assertSee('Vérifier maintenant')
        ->call('checkNow')
        ->assertHasNoErrors();

    Queue::assertPushed(CheckMonitorJob::class, fn (CheckMonitorJob $job): bool => $job->monitorId === $monitor->id);

    expect($monitor->refresh()->next_check_at->equalTo(now()->addMinutes(15)))->toBeTrue();
});

test('monitor detail page can update the current monitor from the edit modal', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'name' => 'Old API',
        'url' => 'https://old.example.com',
        'enabled' => true,
        'interval_minutes' => 5,
        'timeout_seconds' => 10,
        'check_criteria' => [
            ['type' => MonitorCheckCriterionType::HttpStatus->value, 'expected' => 200],
        ],
        'next_check_at' => now()->addHour(),
    ]);

    Livewire::actingAs($user)
        ->test('pages::monitors.show', ['monitor' => $monitor->id])
        ->assertSee('Modifier')
        ->call('openEditMonitorModal', $monitor->id)
        ->assertSet('showEditMonitorModal', true)
        ->assertSet('monitorName', 'Old API')
        ->assertSet('url', 'https://old.example.com')
        ->set('monitorName', 'New API')
        ->set('url', 'https://new.example.com')
        ->set('enabled', false)
        ->set('intervalMinutes', 15)
        ->set('timeoutSeconds', 8)
        ->set('checkCriteria', [
            ['type' => MonitorCheckCriterionType::HttpStatus->value, 'expected' => 204],
            ['type' => MonitorCheckCriterionType::JsonPath->value, 'path' => 'status', 'expected' => 'ok'],
            ['type' => MonitorCheckCriterionType::BodyContains->value, 'text' => 'ready'],
        ])
        ->call('updateMonitor')
        ->assertHasNoErrors()
        ->assertSet('showEditMonitorModal', false)
        ->assertSee('New API')
        ->assertSee('https://new.example.com');

    $monitor->refresh();

    expect($monitor->name)->toBe('New API')
        ->and($monitor->url)->toBe('https://new.example.com')
        ->and($monitor->enabled)->toBeFalse()
        ->and($monitor->interval_minutes)->toBe(15)
        ->and($monitor->timeout_seconds)->toBe(8)
        ->and($monitor->next_check_at->equalTo(now()))->toBeTrue()
        ->and($monitor->check_criteria)->toBe([
            ['type' => MonitorCheckCriterionType::HttpStatus->value, 'expected' => 204],
            ['type' => MonitorCheckCriterionType::JsonPath->value, 'path' => 'status', 'expected' => 'ok'],
            ['type' => MonitorCheckCriterionType::BodyContains->value, 'text' => 'ready'],
        ]);
});

test('monitor detail page displays an overdue next check as now', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'name' => 'API publique',
        'next_check_at' => now()->subMinute(),
    ]);

    $this->actingAs($user)
        ->get(route('monitors.show', $monitor))
        ->assertOk()
        ->assertSee('API publique')
        ->assertSee('maintenant')
        ->assertSee('data-relative-time-mode="due"', false);
});

test('monitor detail chart uses the latest one hundred twenty checks independently from table history', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create();

    CheckResult::factory()->for($monitor)->create([
        'status' => CheckStatus::Up,
        'response_time_ms' => 900,
        'checked_at' => now()->subMinutes(121),
        'checked_url' => $monitor->url,
    ]);

    foreach (range(1, 120) as $minute) {
        $factory = $minute === 40
            ? CheckResult::factory()->down()
            : CheckResult::factory();

        $factory->for($monitor)->create([
            'status' => $minute === 40 ? CheckStatus::Down : CheckStatus::Up,
            'response_time_ms' => $minute === 40 ? 250 : 100 + $minute,
            'checked_at' => now()->subMinutes($minute),
            'checked_url' => $monitor->url,
            'error_message' => $minute === 40 ? 'Unexpected HTTP status 500.' : null,
        ]);
    }

    $this->actingAs($user)
        ->get(route('monitors.show', $monitor))
        ->assertOk()
        ->assertSee('16/07/2026 10:00:00 - OK - 220 ms', false)
        ->assertSee('16/07/2026 11:20:00 - Indisponible - 250 ms - HTTP 500 - Unexpected HTTP status 500.', false)
        ->assertSee('16/07/2026 11:59:00 - OK - 101 ms', false)
        ->assertDontSee('16/07/2026 09:59:00 - OK - 900 ms', false)
        ->assertSee('Unexpected HTTP status 500.')
        ->assertSee('250 ms');
});

test('monitor detail sparkline is rendered without apex charts javascript', function () {
    expect(file_get_contents(resource_path('js/app.js')))
        ->not->toContain('monitor-response-time-chart');
});

test('monitor detail table displays the latest ten checks including failures', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create();

    CheckResult::factory()->for($monitor)->create([
        'status' => CheckStatus::Up,
        'checked_at' => now()->subMinutes(30),
        'checked_url' => $monitor->url,
        'response_excerpt' => 'oldest visible marker',
    ]);

    foreach (range(1, 25) as $minute) {
        CheckResult::factory()->down()->for($monitor)->create([
            'checked_at' => now()->subMinutes($minute),
            'checked_url' => $monitor->url,
            'error_message' => "Failure marker {$minute}",
        ]);
    }

    $this->actingAs($user)
        ->get(route('monitors.show', $monitor))
        ->assertOk()
        ->assertSee('Inclure les succès')
        ->assertSee('data-flux-pagination', false);

    $component = Livewire::actingAs($user)
        ->test('pages::monitors.show', ['monitor' => $monitor->id]);

    $firstPage = $component->instance()->checkResults();

    expect($firstPage->perPage())->toBe(10)
        ->and($firstPage->total())->toBe(26)
        ->and(collect($firstPage->items())->map(fn (CheckResult $checkResult): ?string => $checkResult->error_message ?? $checkResult->response_excerpt)->all())->toBe([
            'Failure marker 1',
            'Failure marker 2',
            'Failure marker 3',
            'Failure marker 4',
            'Failure marker 5',
            'Failure marker 6',
            'Failure marker 7',
            'Failure marker 8',
            'Failure marker 9',
            'Failure marker 10',
        ]);

    $component->call('setPage', 2);

    expect(collect($component->instance()->checkResults()->items())->map(fn (CheckResult $checkResult): ?string => $checkResult->error_message ?? $checkResult->response_excerpt)->all())->toBe([
        'Failure marker 11',
        'Failure marker 12',
        'Failure marker 13',
        'Failure marker 14',
        'Failure marker 15',
        'Failure marker 16',
        'Failure marker 17',
        'Failure marker 18',
        'Failure marker 19',
        'Failure marker 20',
    ]);

    $component->call('setPage', 3);

    expect(collect($component->instance()->checkResults()->items())->map(fn (CheckResult $checkResult): ?string => $checkResult->error_message ?? $checkResult->response_excerpt)->all())->toBe([
        'Failure marker 21',
        'Failure marker 22',
        'Failure marker 23',
        'Failure marker 24',
        'Failure marker 25',
        'oldest visible marker',
    ]);
});

test('monitor detail history can exclude successful checks', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-16 12:00:00'));

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create();

    foreach (range(1, 12) as $minute) {
        CheckResult::factory()->for($monitor)->create([
            'status' => CheckStatus::Up,
            'checked_at' => now()->subMinutes($minute),
            'checked_url' => $monitor->url,
            'response_excerpt' => "Success marker {$minute}",
        ]);

        CheckResult::factory()->down()->for($monitor)->create([
            'checked_at' => now()->subMinutes($minute)->subSeconds(30),
            'checked_url' => $monitor->url,
            'error_message' => "Failure marker {$minute}",
        ]);
    }

    $component = Livewire::actingAs($user)
        ->test('pages::monitors.show', ['monitor' => $monitor->id])
        ->assertSet('includeSuccessfulCheckResults', true)
        ->assertSee('Success marker 1')
        ->assertSee('Failure marker 1')
        ->set('includeSuccessfulCheckResults', false)
        ->assertSet('includeSuccessfulCheckResults', false)
        ->assertSee('Failure marker 1')
        ->assertSee('Failure marker 10');

    $filteredPage = $component->instance()->checkResults();

    expect($filteredPage->perPage())->toBe(10)
        ->and($filteredPage->total())->toBe(12)
        ->and(collect($filteredPage->items())->every(fn (CheckResult $checkResult): bool => $checkResult->status->isFailure()))->toBeTrue()
        ->and(collect($filteredPage->items())->map(fn (CheckResult $checkResult): ?string => $checkResult->error_message)->all())->toBe([
            'Failure marker 1',
            'Failure marker 2',
            'Failure marker 3',
            'Failure marker 4',
            'Failure marker 5',
            'Failure marker 6',
            'Failure marker 7',
            'Failure marker 8',
            'Failure marker 9',
            'Failure marker 10',
        ]);
});

test('monitor detail page refreshes when a monitor check completed event is received', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'name' => 'API publique',
    ]);

    $component = Livewire::actingAs($user)
        ->test('pages::monitors.show', ['monitor' => $monitor->id])
        ->assertSee('Aucune vérification enregistrée pour cette URL.');

    $result = CheckResult::factory()->for($monitor)->create([
        'status' => CheckStatus::Up,
        'http_status' => 200,
        'response_time_ms' => 42,
        'checked_at' => now(),
        'checked_url' => $monitor->url,
    ]);

    $monitor->forceFill([
        'current_status' => MonitorStatus::Up,
        'last_checked_at' => $result->checked_at,
        'last_success_at' => $result->checked_at,
        'next_check_at' => $result->checked_at->copy()->addMinutes($monitor->interval_minutes),
    ])->save();

    $component
        ->call('refreshAfterMonitorCheckCompleted', ['monitorId' => $monitor->id])
        ->assertSee('42 ms')
        ->assertSee('data-monitor-response-time-sparkline', false)
        ->assertSee('data-relative-time=', false);
});

test('monitor detail page is not available for another user monitor', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->for($otherUser)->create();
    $monitor = Monitor::factory()->for($project)->create();

    $this->actingAs($user)
        ->get(route('monitors.show', $monitor))
        ->assertNotFound();
});

test('monitor detail page shows an empty history state', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create(['name' => 'Status page']);

    $this->actingAs($user)
        ->get(route('monitors.show', $monitor))
        ->assertOk()
        ->assertSee('Status page')
        ->assertSee('Aucune vérification enregistrée pour cette URL.');
});
