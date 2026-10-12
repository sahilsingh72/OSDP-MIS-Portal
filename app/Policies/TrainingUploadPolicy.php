<?php

namespace App\Policies;

use App\Models\TrainingUpload;
use App\Models\User;

class TrainingUploadPolicy
{
    /**
     * Determine whether the user can view the upload list.
     */
    public function view(User $user, TrainingUpload $upload): bool
    {
        return $this->download($user, $upload);
    }

    /**
     * Determine whether the user can download / stream private evidence file.
     */
    public function download(User $user, TrainingUpload $upload): bool
    {
        if (!$user->can('evidence.download') && !$user->can('evidence.view')) {
            return false;
        }

        $batch = $upload->batch;
        if (!$batch) {
            return false;
        }

        if ($user->hasRole(['super-admin', 'ocac', 'pmu'])) {
            return true;
        }

        if ($user->hasRole('district-coordinator')) {
            return $user->isAssignedToDistrict($batch->district_id);
        }

        if ($user->hasRole('field-trainer')) {
            return $user->isAssignedToBatch($batch->id);
        }

        if ($user->hasRole('field-monitor')) {
            return $user->isAssignedToDistrict($batch->district_id) || $batch->fieldVerifications()->where('monitor_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can delete / invalidate the upload.
     */
    public function delete(User $user, TrainingUpload $upload): bool
    {
        $batch = $upload->batch;
        if (!$batch || in_array($batch->status, ['verified', 'closed'])) {
            return false; // Verified batches are locked
        }

        if ($user->hasRole(['super-admin', 'pmu'])) {
            return true;
        }

        if ($user->hasRole('district-coordinator')) {
            return $user->isAssignedToDistrict($batch->district_id);
        }

        if ($user->hasRole('field-trainer')) {
            return $upload->uploaded_by === $user->id && in_array($batch->status, ['scheduled', 'in_progress', 'awaiting_evidence', 'correction_required']);
        }

        return false;
    }
}
