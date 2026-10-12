<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogPolicy
{
    /**
     * Determine whether the user can view audit logs.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('audit-logs.view');
    }

    /**
     * Determine whether the user can view the specific audit log.
     */
    public function view(User $user, AuditLog $auditLog): bool
    {
        return $user->can('audit-logs.view');
    }
}
