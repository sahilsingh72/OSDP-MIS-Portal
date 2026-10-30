<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipantFeedback extends Model
{
    use HasFactory;

    protected $table = 'participant_feedback';

    protected $fillable = [
        'training_batch_id',
        'batch_participant_id',
        'participant_id',
        'training_tier_snapshot',
        'venue_snapshot',
        'session_date',
        'designation_office_snapshot',
        'participant_name_snapshot',
        'q1_clarity',
        'q2_knowledge',
        'q3_relevance',
        'q4_handbook',
        'q5_overall',
        'average_rating',
        'q6_most_useful',
        'q7_improvements',
        'q8_unanswered',
        'entered_by',
    ];

    protected $casts = [
        'session_date' => 'date',
        'q1_clarity' => 'integer',
        'q2_knowledge' => 'integer',
        'q3_relevance' => 'integer',
        'q4_handbook' => 'integer',
        'q5_overall' => 'integer',
        'average_rating' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (ParticipantFeedback $feedback) {
            $scores = array_filter([
                $feedback->q1_clarity,
                $feedback->q2_knowledge,
                $feedback->q3_relevance,
                $feedback->q4_handbook,
                $feedback->q5_overall,
            ], fn($v) => !is_null($v));

            if (!empty($scores)) {
                $feedback->average_rating = round(array_sum($scores) / count($scores), 2);
            }
        });
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(TrainingBatch::class, 'training_batch_id');
    }

    public function batchParticipant(): BelongsTo
    {
        return $this->belongsTo(BatchParticipant::class, 'batch_participant_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
