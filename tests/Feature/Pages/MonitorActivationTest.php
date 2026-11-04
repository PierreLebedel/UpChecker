<?php

use App\Actions\CheckMonitorAction;
use App\Enums\MonitorStatus;
use App\Jobs\CheckMonitorJob;
use App\Models\CheckResult;
use App\Models\Monitor;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

dataset('monitor editing pages', [
    'projects list' => 'pages::projects.index',
    'project detail' => 'pages::projects.show',
    'monitor detail' => 'pages::monitors.show',
]);

test('disabled monitors remain visible with a light grey status and their history', function (string $route) {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->disabled()->for($project)->create([
        'name' => 'Paused API',
        'current_status' => MonitorStatus::Down,
        'next_check_at' => now()->subMinute(),
    ]);
    CheckResult::factory()->down()->for($monitor)->create([
        'checked_at' => now()->subMinute(),
        'error_message' => 'Preserved failure history',
    ]);
    $parameters = match ($route) {
        'projects.show' => [$project],
        'monitors.show' => [$monitor],
        default => [],
    };

    $response = $this->actingAs($user)
        ->get(route($route, $parameters))
        ->assertOk()
        ->assertSee('Paused API')
        ->assertSee($monitor->url)
        ->assertSee('Statut : Désactivé', false)
        ->assertSee('bg-zinc-300!', false)
        ->assertSee('dark:bg-zinc-300!', false)
        ->assertSee('En pause');

    if ($route === 'dashboard') {
        $response->assertSee('Statut actuel : Désactivé', false)
            ->assertDontSee('Statut actuel : Indisponible', false)
            ->assertSee('Preserved failure history');
    }

    if ($route === 'monitors.show') {
        $response->assertSee('Preserved failure history');
    }

    expect($monitor->refresh()->current_status)->toBe(MonitorStatus::Down)
        ->and($monitor->checkResults()->count())->toBe(1);
})->with(['dashboard', 'projects.index', 'projects.show', 'monitors.show']);

test('monitor edit modals can pause and resume checks without losing the last status', function (string $component) {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create([
        'current_status' => MonitorStatus::Up,
        'last_checked_at' => now()->subMinutes(5),
    ]);
    $lastCheckedAt = $monitor->last_checked_at;
    $parameters = match ($component) {
        'pages::projects.show' => ['project' => $project->id],
        'pages::monitors.show' => ['monitor' => $monitor->id],
        default => [],
    };

    $page = Livewire::actingAs($user)->test($component, $parameters)
        ->call('openEditMonitorModal', $monitor->id)
        ->assertSet('enabled', true)
        ->assertSee('Contrôle actif')
        ->assertSee('wire:model="enabled"', false)
        ->set('enabled', false)
        ->call('updateMonitor')
        ->assertHasNoErrors()
        ->assertSee('Statut : Désactivé', false)
        ->assertSee('En pause');

    expect($monitor->refresh()->enabled)->toBeFalse()
        ->and($monitor->current_status)->toBe(MonitorStatus::Up)
        ->and($monitor->last_checked_at->equalTo($lastCheckedAt))->toBeTrue()
        ->and(Monitor::query()->due()->whereKey($monitor->id)->exists())->toBeFalse();

    $page->call('openEditMonitorModal', $monitor->id)
        ->assertSet('enabled', false)
        ->set('enabled', true)
        ->call('updateMonitor')
        ->assertHasNoErrors()
        ->assertSee('Statut : OK', false)
        ->assertDontSee('Statut : Désactivé', false);

    expect($monitor->refresh()->enabled)->toBeTrue()
        ->and($monitor->current_status)->toBe(MonitorStatus::Up)
        ->and(Monitor::query()->due()->whereKey($monitor->id)->exists())->toBeTrue();
})->with('monitor editing pages');

test('manual checks cannot be dispatched after a monitor has been disabled', function (string $component) {
    Queue::fake();
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create(['next_check_at' => now()->addHour()]);
    $nextCheckAt = $monitor->next_check_at;
    $parameters = match ($component) {
        'pages::projects.show' => ['project' => $project->id],
        'pages::monitors.show' => ['monitor' => $monitor->id],
        default => [],
    };
    $page = Livewire::actingAs($user)->test($component, $parameters);
    $monitor->update(['enabled' => false]);

    $page->call('checkNow', ...($component === 'pages::monitors.show' ? [] : [$monitor->id]))
        ->assertHasNoErrors();

    Queue::assertNotPushed(CheckMonitorJob::class);
    expect($monitor->refresh()->next_check_at->equalTo($nextCheckAt))->toBeTrue();
})->with('monitor editing pages');

test('checks queued before disabling a monitor are skipped and resume once enabled', function () {
    Queue::fake();
    Http::fake(['*' => Http::response('ok')]);
    $monitor = Monitor::factory()->due()->create();
    $job = new CheckMonitorJob($monitor->id);
    $monitor->update(['enabled' => false]);

    $this->artisan('monitors:dispatch-due-checks')->assertSuccessful();
    $job->handle(app(CheckMonitorAction::class));

    Queue::assertNotPushed(CheckMonitorJob::class);
    Http::assertNothingSent();
    expect($monitor->checkResults()->count())->toBe(0);

    $monitor->update(['enabled' => true]);
    $this->artisan('monitors:dispatch-due-checks')->assertSuccessful();
    Queue::assertPushed(CheckMonitorJob::class, fn (CheckMonitorJob $queuedJob): bool => $queuedJob->monitorId === $monitor->id);
    $job->handle(app(CheckMonitorAction::class));

    Http::assertSentCount(1);
    expect($monitor->checkResults()->count())->toBe(1)
        ->and($monitor->refresh()->current_status)->toBe(MonitorStatus::Up);
});
