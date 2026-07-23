<?php

use App\Models\Monitor;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return hash_equals((string) $user->getAuthIdentifier(), (string) $id);
});

Broadcast::channel('users.{userId}', function ($user, string $userId): bool {
    return hash_equals((string) $user->getAuthIdentifier(), $userId);
});

Broadcast::channel('monitors.{monitorId}', function ($user, string $monitorId): bool {
    return Monitor::query()
        ->join('projects', 'projects.id', '=', 'monitors.project_id')
        ->whereKey($monitorId)
        ->where('projects.user_id', $user->getAuthIdentifier())
        ->exists();
});
