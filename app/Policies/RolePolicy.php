<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    /**
     * Protected core roles that cannot be deleted.
     *
     * @var array<string>
     */
    protected array $protectedRoles = [
        'super-admin',
        'ocac',
        'pmu',
        'district-coordinator',
        'field-trainer',
        'field-monitor',
    ];

    /**
     * Determine whether the user can view any roles.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('roles.manage');
    }

    /**
     * Determine whether the user can view the role.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->can('roles.manage');
    }

    /**
     * Determine whether the user can create roles.
     */
    public function create(User $user): bool
    {
        return $user->can('roles.manage');
    }

    /**
     * Determine whether the user can update the role / assign permissions.
     */
    public function update(User $user, Role $role): bool
    {
        return $user->can('roles.manage');
    }

    /**
     * Determine whether the user can delete the role.
     */
    public function delete(User $user, Role $role): bool
    {
        if (in_array($role->name, $this->protectedRoles, true)) {
            return false;
        }

        return $user->can('roles.manage') && $role->users()->count() === 0;
    }
}
