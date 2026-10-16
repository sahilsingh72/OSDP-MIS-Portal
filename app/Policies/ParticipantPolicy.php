<?php

namespace App\Policies;

use App\Models\Participant;
use App\Models\User;

class ParticipantPolicy
{
    /**
     * Determine whether the user can view any participants.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('participants.view');
    }

    /**
     * Determine whether the user can view the participant.
     */
    public function view(User $user, Participant $participant): bool
    {
        return $user->can('participants.view');
    }

    /**
     * Determine whether the user can create / import participants.
     */
    public function create(User $user): bool
    {
        return $user->can('participants.manage');
    }

    /**
     * Determine whether the user can update the participant.
     * Super admin can update any participant.
     * Users with manage permission can update participants they created (or unassigned).
     * If assigned to a batch, the batch's district coordinator, trainer, or monitor can update.
     */
    public function update(User $user, Participant $participant): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        if (!$user->can('participants.manage')) {
            return false;
        }

        if ($participant->created_by === $user->id || is_null($participant->created_by)) {
            return true;
        }

        return $this->isAssignedToUserBatch($user, $participant);
    }

    /**
     * Determine whether the user can delete the participant.
     * Super admin can delete any participant.
     * Users with manage permission can delete participants they created.
     * If assigned to a batch, the batch's district coordinator, trainer, or monitor can delete.
     */
    public function delete(User $user, Participant $participant): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        if (!$user->can('participants.manage')) {
            return false;
        }

        if ($participant->created_by === $user->id) {
            return true;
        }

        return $this->isAssignedToUserBatch($user, $participant);
    }

    /**
     * Determine whether the user can restore the participant.
     * Super admin can restore any participant.
     * Users with manage permission can restore participants they created.
     * If assigned to a batch, the batch's district coordinator, trainer, or monitor can restore.
     */
    public function restore(User $user, Participant $participant): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        if (!$user->can('participants.manage')) {
            return false;
        }

        if ($participant->created_by === $user->id) {
            return true;
        }

        return $this->isAssignedToUserBatch($user, $participant);
    }

    /**
     * Check if participant is enrolled in any batch assigned to user as:
     * - District Coordinator of the batch's district
     * - Trainer assigned to the batch
     * - Monitor assigned to the batch or district
     */
    public function isAssignedToUserBatch(User $user, Participant $participant): bool
    {
        if ($user->hasRole(['super-admin', 'ocac', 'pmu'])) {
            return true;
        }

        return $participant->batches()
            ->where(function ($query) use ($user) {
                // 1. District Coordinator for the batch's district
                $assignedDistrictIds = $user->assignedDistricts()->pluck('districts.id')->toArray();
                if (!empty($assignedDistrictIds)) {
                    $query->whereIn('training_batches.district_id', $assignedDistrictIds);
                }

                // 2. Trainer assigned to the batch
                $query->orWhereHas('trainers', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });

                // 3. Monitor assigned to the batch (via field verification)
                $query->orWhereHas('fieldVerifications', function ($q) use ($user) {
                    $q->where('monitor_id', $user->id);
                });
            })
            ->exists();
    }
}
