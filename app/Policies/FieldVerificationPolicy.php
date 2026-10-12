<?php

namespace App\Policies;

use App\Models\FieldVerification;
use App\Models\User;

class FieldVerificationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('field-verifications.view');
    }

    public function view(User $user, FieldVerification $verification): bool
    {
        if (!$user->can('field-verifications.view')) {
            return false;
        }

        if ($user->hasRole(['super-admin', 'ocac', 'pmu'])) {
            return true;
        }

        $batch = $verification->batch;
        if (!$batch) {
            return false;
        }

        if ($user->hasRole('district-coordinator')) {
            return $user->isAssignedToDistrict($batch->district_id);
        }

        if ($user->hasRole('field-monitor')) {
            return $verification->monitor_id === $user->id || $user->isAssignedToDistrict($batch->district_id);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('field-verifications.create');
    }
}
