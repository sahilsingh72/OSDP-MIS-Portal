<?php

namespace App\Policies;

use App\Models\SystemSetting;
use App\Models\User;

class SystemSettingPolicy
{
    /**
     * Determine whether the user can view system settings.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('settings.manage') || $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can update system settings.
     */
    public function update(User $user): bool
    {
        return $user->can('settings.manage') || $user->hasRole('super-admin');
    }
}
