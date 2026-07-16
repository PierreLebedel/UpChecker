<?php

use App\Enums\AlertChannel;
use App\Enums\AlertDeliveryStatus;
use App\Enums\CheckStatus;
use App\Enums\MonitorStatus;
use App\Models\AlertDelivery;
use App\Models\CheckResult;
use App\Models\Monitor;
use App\Models\Project;
use App\Models\User;

test('domain models use uuid identifiers and relationships', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $monitor = Monitor::factory()->for($project)->create();
    $checkResult = CheckResult::factory()->for($monitor)->create();
    $alertDelivery = AlertDelivery::factory()
        ->for($monitor)
        ->for($checkResult)
        ->create();

    expect($user->id)->toBeString()->toHaveLength(36)
        ->and($project->id)->toBeString()->toHaveLength(36)
        ->and($monitor->id)->toBeString()->toHaveLength(36)
        ->and($checkResult->id)->toBeString()->toHaveLength(36)
        ->and($alertDelivery->id)->toBeString()->toHaveLength(36)
        ->and($project->user->is($user))->toBeTrue()
        ->and($monitor->project->is($project))->toBeTrue()
        ->and($checkResult->monitor->is($monitor))->toBeTrue()
        ->and($alertDelivery->monitor->is($monitor))->toBeTrue()
        ->and($alertDelivery->checkResult->is($checkResult))->toBeTrue();
});

test('monitor defaults are aligned with the product plan', function () {
    $monitor = Monitor::factory()->create();

    expect($monitor->enabled)->toBeTrue()
        ->and($monitor->interval_minutes)->toBe(5)
        ->and($monitor->timeout_seconds)->toBe(10)
        ->and($monitor->expected_http_status)->toBe(200)
        ->and($monitor->current_status)->toBe(MonitorStatus::Unknown);
});

test('monitor due scope returns enabled monitors ready to be checked', function () {
    $due = Monitor::factory()->due()->create();
    Monitor::factory()->notDue()->create();
    Monitor::factory()->disabled()->due()->create();

    expect(Monitor::due()->pluck('id')->all())->toBe([$due->id]);
});

test('enum failure helpers expose monitoring semantics', function () {
    expect(MonitorStatus::Up->isFailure())->toBeFalse()
        ->and(MonitorStatus::Down->isFailure())->toBeTrue()
        ->and(MonitorStatus::Timeout->isFailure())->toBeTrue()
        ->and(MonitorStatus::Invalid->isFailure())->toBeTrue()
        ->and(CheckStatus::Up->isFailure())->toBeFalse()
        ->and(CheckStatus::Down->isFailure())->toBeTrue();
});

test('alert delivery defaults to mail and pending', function () {
    $alertDelivery = AlertDelivery::factory()->create();

    expect($alertDelivery->channel)->toBe(AlertChannel::Mail)
        ->and($alertDelivery->status)->toBe(AlertDeliveryStatus::Pending);
});
