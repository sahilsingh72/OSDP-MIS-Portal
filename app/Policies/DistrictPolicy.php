<?php

namespace App\Policies;

use App\Models\District;
use App\Models\User;

class DistrictPolicy
{
    /**
     * Determine whether the user can view any districts (master data).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('master-data.view');
    }

    /**
     * Determine whether the user can view the district.
     */
    public function view(User $user, District $district): bool
    {
        return $user->can('master-data.view');
    }

    /**
     * Determine whether the user can manage master data.
     */
    public function manage(User $user): bool
    {
        return $user->can('master-data.manage');
    }
}
