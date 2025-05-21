<?php

namespace App\Providers;

use App\Models\Circuit;
use App\Models\Driver;
use App\Models\User;
use App\Policies\Api\CircuitPolicy;
use App\Policies\Api\DriverPolicy;
use App\Policies\Api\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::policy(Driver::class, DriverPolicy::class);
        Gate::policy(Circuit::class, CircuitPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}