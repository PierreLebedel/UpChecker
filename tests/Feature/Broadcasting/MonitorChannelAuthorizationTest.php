<?php

use App\Models\Monitor;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

test('monitor private channel is only available to the project owner', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->for($owner)->create();
    $monitor = Monitor::factory()->for($project)->create();
    $channel = Broadcast::connection()->getChannels()->get('monitors.{monitorId}');

    expect($channel)->toBeCallable()
        ->and($channel($owner, $monitor->id))->toBeTrue()
        ->and($channel($otherUser, $monitor->id))->toBeFalse();
});

test('user private channel is only available to the authenticated user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $channel = Broadcast::connection()->getChannels()->get('users.{userId}');

    expect($channel)->toBeCallable()
        ->and($channel($user, $user->id))->toBeTrue()
        ->and($channel($otherUser, $user->id))->toBeFalse();
});
