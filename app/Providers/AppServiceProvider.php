<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\District;
use App\Models\SystemSetting;
use App\Models\TrainingModule;
use App\Models\User;
use App\Models\Venue;
use App\Policies\AuditLogPolicy;
use App\Policies\DistrictPolicy;
use App\Policies\RolePolicy;
use App\Policies\SystemSettingPolicy;
use App\Policies\TrainingModulePolicy;
use App\Policies\UserPolicy;
use App\Policies\VenuePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

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
        Gate::policy(TrainingModule::class, TrainingModulePolicy::class);
        Gate::policy(Venue::class, VenuePolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(\App\Models\Participant::class, \App\Policies\ParticipantPolicy::class);

        // Implicitly grant "super-admin" role all abilities except specific field workflow actions (e.g. submitting batches, entering field summaries)
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super-admin')) {
                if (in_array($ability, ['submit', 'manageSummary'])) {
                    return null; // Defer to policy
                }
                return true;
            }
            return null;
        });
    }
}
