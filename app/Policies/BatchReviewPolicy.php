<?php

namespace App\Policies;

use App\Models\BatchReview;
use App\Models\User;

class BatchReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('reviews.manage') || $user->can('evidence.review') || $user->can('batches.view');
    }

    public function create(User $user): bool
    {
        return $user->can('reviews.manage') || $user->can('evidence.review');
    }
}
