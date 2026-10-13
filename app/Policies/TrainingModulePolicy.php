<?php

namespace App\Policies;

use App\Models\TrainingModule;
use App\Models\User;

class TrainingModulePolicy
{
    /**
     * Determine whether the user can view any training modules.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('master-data.view') || $user->can('master-data.manage');
    }

    /**
     * Determine whether the user can view the training module.
     */
    public function view(User $user, TrainingModule $module): bool
    {
        return $user->can('master-data.view') || $user->can('master-data.manage');
    }

    /**
     * Determine whether the user can create training modules.
     */
    public function create(User $user): bool
    {
        return $user->can('master-data.manage');
    }

    /**
     * Determine whether the user can update the training module.
     */
    public function update(User $user, TrainingModule $module): bool
    {
        return $user->can('master-data.manage');
    }

    /**
     * Determine whether the user can delete the training module.
     */
    public function delete(User $user, TrainingModule $module): bool
    {
        return $user->can('master-data.manage');
    }
}
