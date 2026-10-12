<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\District;
use App\Models\SystemSetting;
use App\Models\User;
use App\Policies\AuditLogPolicy;
use App\Policies\DistrictPolicy;
use App\Policies\SystemSettingPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(District::class, DistrictPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(SystemSetting::class, SystemSettingPolicy::class);

        // Implicitly grant "super-admin" role all abilities
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });
    }
}
