<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchReview extends Model
{
    use HasFactory;

    public const DECISION_VERIFIED = 'verified';
    public const DECISION_CORRECTION = 'correction_requested';
    public const DECISION_REJECTED = 'rejected';
    public const DECISION_PACK_CHECKED = 'pack_checked';

    protected $fillable = [
        'training_batch_id',
        'reviewer_id',
        'role_at_review',
        'decision',
        'remarks',
        'checklist_snapshot',
    ];

    protected $casts = [
        'checklist_snapshot' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(TrainingBatch::class, 'training_batch_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
