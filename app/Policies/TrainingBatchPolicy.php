<?php

namespace App\Policies;

use App\Models\TrainingBatch;
use App\Models\User;

class TrainingBatchPolicy
{
    /**
     * Determine whether the user can view any batches.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('batches.view');
    }

    /**
     * Determine whether the user can view the batch.
     */
    public function view(User $user, TrainingBatch $batch): bool
    {
        if (!$user->can('batches.view')) {
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
     * Determine whether the user can create batches.
     */
    public function create(User $user): bool
    {
        return $user->can('batches.create');
    }

    /**
     * Determine whether the user can update the batch.
     */
    public function update(User $user, TrainingBatch $batch): bool
    {
        if (!$user->can('batches.update')) {
            return false;
        }

        if ($user->hasRole(['super-admin', 'pmu'])) {
            return true;
        }

        if ($user->hasRole('district-coordinator')) {
            return $user->isAssignedToDistrict($batch->district_id);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the batch.
     */
    public function delete(User $user, TrainingBatch $batch): bool
    {
        if (!$user->hasRole(['super-admin', 'pmu'])) {
            return false;
        }

        // Only batches that have not progressed to verification can be deleted
        return in_array($batch->status, [TrainingBatch::STATUS_PLANNED, TrainingBatch::STATUS_SCHEDULED]);
    }

    /**
     * Determine whether the user can upload evidence for the batch.
     */
    public function uploadEvidence(User $user, TrainingBatch $batch): bool
    {
        if (!$user->can('evidence.upload')) {
            return false;
        }

        if ($user->hasRole(['super-admin', 'pmu'])) {
            return true;
        }

        if ($user->hasRole('district-coordinator')) {
            return $user->isAssignedToDistrict($batch->district_id);
        }

        if ($user->hasRole('field-trainer')) {
            return $user->isAssignedToBatch($batch->id) && in_array($batch->status, [
                TrainingBatch::STATUS_SCHEDULED,
                TrainingBatch::STATUS_IN_PROGRESS,
                TrainingBatch::STATUS_AWAITING_EVIDENCE,
                TrainingBatch::STATUS_CORRECTION_REQUIRED,
            ]);
        }

        return false;
    }

    /**
     * Determine whether the user can submit the batch for review.
     * Field Trainers and District Coordinators submit for review;
     * Super Admin, PMU, and OCAC review/verify batches instead.
     */
    public function submit(User $user, TrainingBatch $batch): bool
    {
        if ($user->hasRole(['super-admin', 'pmu', 'ocac'])) {
            return false;
        }

        if ($user->hasRole('district-coordinator')) {
            return $user->isAssignedToDistrict($batch->district_id);
        }

        if ($user->hasRole('field-trainer')) {
            return $user->isAssignedToBatch($batch->id);
        }

        return false;
    }

    /**
     * Determine whether the user can review the batch.
     */
    public function review(User $user, TrainingBatch $batch): bool
    {
        if ($user->hasRole('field-trainer')) {
            // Trainers can NEVER review or approve their own batches
            return false;
        }

        if ($user->hasRole(['super-admin', 'pmu'])) {
            return $user->can('reviews.manage');
        }

        if ($user->hasRole('district-coordinator')) {
            return $user->can('evidence.review') && $user->isAssignedToDistrict($batch->district_id);
        }

        return false;
    }

    /**
     * Determine whether the user can enter/edit field training summary and feedback evaluation.
     * Field Trainers and District Coordinators enter summaries from the field;
     * Super Admin, PMU, and OCAC view them in read-only mode.
     */
    public function manageSummary(User $user, TrainingBatch $batch): bool
    {
        if ($user->hasRole(['super-admin', 'pmu', 'ocac'])) {
            return false;
        }

        if (!in_array($batch->status, [
            TrainingBatch::STATUS_SCHEDULED,
            TrainingBatch::STATUS_IN_PROGRESS,
            TrainingBatch::STATUS_AWAITING_EVIDENCE,
            TrainingBatch::STATUS_CORRECTION_REQUIRED,
        ])) {
            return false;
        }

        if ($user->hasRole('district-coordinator')) {
            return $user->isAssignedToDistrict($batch->district_id);
        }

        if ($user->hasRole('field-trainer')) {
            return $user->isAssignedToBatch($batch->id);
        }

        return false;
    }
}
