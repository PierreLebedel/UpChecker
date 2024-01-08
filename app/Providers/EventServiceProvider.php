<?php

namespace App\Providers;

use App\Events\CheckupCreatedEvent;
use App\Listeners\CheckupCreatedListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(
            Registered::class,
            SendEmailVerificationNotification::class
        );

        Event::listen(
            CheckupCreatedEvent::class,
            CheckupCreatedListener::class
        );

    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
