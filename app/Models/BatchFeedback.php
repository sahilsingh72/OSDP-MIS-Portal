<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchFeedback extends Model
{
    use HasFactory;

    protected $table = 'batch_feedback';

    protected $fillable = [
        'training_batch_id',
        'q1_clarity_avg',
        'q2_knowledge_avg',
        'q3_relevance_avg',
        'q4_handbook_avg',
        'q5_overall_avg',
        'total_forms_analyzed',
        'qualitative_highlights',
        'improvement_suggestions',
        'unanswered_questions',
    ];

    protected $casts = [
        'q1_clarity_avg' => 'decimal:2',
        'q2_knowledge_avg' => 'decimal:2',
        'q3_relevance_avg' => 'decimal:2',
        'q4_handbook_avg' => 'decimal:2',
        'q5_overall_avg' => 'decimal:2',
        'total_forms_analyzed' => 'integer',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(TrainingBatch::class, 'training_batch_id');
    }
}
