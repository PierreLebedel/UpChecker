<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Checkup;
use App\Models\Endpoint;
use App\Models\Project;
use App\Policies\CheckupPolicy;
use App\Policies\EndpointPolicy;
use App\Policies\ProjectPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Project::class  => ProjectPolicy::class,
        Endpoint::class => EndpointPolicy::class,
        Checkup::class  => CheckupPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
