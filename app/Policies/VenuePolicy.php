<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Venue;

class VenuePolicy
{
    /**
     * Determine whether the user can view any venues.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('master-data.view') || $user->can('master-data.manage');
    }

    /**
     * Determine whether the user can view the venue.
     */
    public function view(User $user, Venue $venue): bool
    {
        return $user->can('master-data.view') || $user->can('master-data.manage');
    }

    /**
     * Determine whether the user can create venues (Super Admin & PMU).
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['super-admin', 'pmu']);
    }

    /**
     * Determine whether the user can update the venue (Super Admin & PMU).
     */
    public function update(User $user, Venue $venue): bool
    {
        return $user->hasRole(['super-admin', 'pmu']);
    }

    /**
     * Determine whether the user can delete the venue (Super Admin & PMU).
     */
    public function delete(User $user, Venue $venue): bool
    {
        return $user->hasRole(['super-admin', 'pmu']);
    }
}
