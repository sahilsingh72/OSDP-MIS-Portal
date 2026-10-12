<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_batch_id',
        'registered_count',
        'present_count',
        'feedback_forms_received',
        'actual_start_time',
        'actual_end_time',
        'pre_post_assessment_done',
        'what_went_well',
        'problems_faced',
        'follow_up_actions',
        'submitted_by_name',
        'checked_by_name',
        'checked_date',
    ];

    protected $casts = [
        'pre_post_assessment_done' => 'boolean',
        'registered_count' => 'integer',
        'present_count' => 'integer',
        'feedback_forms_received' => 'integer',
        'checked_date' => 'date',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(TrainingBatch::class, 'training_batch_id');
    }
}
