<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipantAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_batch_id',
        'batch_participant_id',
        'participant_id',
        'training_tier_snapshot',
        'venue_snapshot',
        'session_date',
        'designation_office_snapshot',
        'participant_name_snapshot',
        'pre_q1',
        'pre_q2',
        'pre_q3',
        'pre_q4',
        'pre_q5',
        'pre_q6',
        'pre_score',
        'pre_completed',
        'post_q1',
        'post_q2',
        'post_q3',
        'post_q4',
        'post_q5',
        'post_q6',
        'post_score',
        'post_completed',
        'entered_by',
    ];

    protected $casts = [
        'session_date' => 'date',
        'pre_q1' => 'boolean',
        'pre_q2' => 'boolean',
        'pre_q3' => 'boolean',
        'pre_q4' => 'boolean',
        'pre_q5' => 'boolean',
        'pre_q6' => 'boolean',
        'pre_score' => 'integer',
        'pre_completed' => 'boolean',
        'post_score' => 'integer',
        'post_completed' => 'boolean',
    ];

    /**
     * PART A: Pre-Assessment Questions (Yes / No).
     */
    public const PRE_QUESTIONS = [
        1 => 'Have you heard of the Odisha State Data Policy (OSDP) 2.0?',
        2 => 'Do you know that the Digital Personal Data Protection (DPDP) Act, 2023 exists?',
        3 => 'Do you know what "personal data" means?',
        4 => 'Do you know that government data is placed in access categories (Open, Registered, Restricted)?',
        5 => 'Do you know what to do if you suspect a data breach?',
        6 => 'Do you know where a citizen can complain if their data is misused?',
    ];

    /**
     * PART B: Post-Assessment Questions (Multiple Choice A, B, C, D).
     */
    public const POST_QUESTIONS = [
        1 => [
            'question' => 'Personal data under OSDP 2.0 includes which of the following?',
            'options' => [
                'A' => 'Only Aadhaar numbers',
                'B' => 'Any information that can identify a person',
                'C' => 'Only financial records',
                'D' => 'None of the above',
            ],
            'correct' => 'B',
        ],
        2 => [
            'question' => 'You are unsure how to classify a dataset. What should you do?',
            'options' => [
                'A' => 'Classify it as Open Access',
                'B' => 'Leave it unclassified',
                'C' => 'Choose the more protective level and ask your Data Steward',
                'D' => 'Delete it',
            ],
            'correct' => 'C',
        ],
        3 => [
            'question' => 'Before data goes to another department, what is required?',
            'options' => [
                'A' => 'Nothing; sharing is always allowed',
                'B' => 'A documented and approved request',
                'C' => 'A verbal request from a senior officer',
                'D' => 'Sharing is never allowed',
            ],
            'correct' => 'B',
        ],
        4 => [
            'question' => 'Within how many hours must citizens be informed of a personal data breach?',
            'options' => [
                'A' => '24 hours',
                'B' => '48 hours',
                'C' => '72 hours',
                'D' => '7 days',
            ],
            'correct' => 'C',
        ],
        5 => [
            'question' => 'What is the overall limit for resolving a citizen’s complaint?',
            'options' => [
                'A' => '30 days',
                'B' => '60 days',
                'C' => '90 days',
                'D' => '180 days',
            ],
            'correct' => 'A',
        ],
        6 => [
            'question' => 'One person’s name is spelled two ways in two registers. Which quality test fails?',
            'options' => [
                'A' => 'Accuracy',
                'B' => 'Completeness',
                'C' => 'Consistency',
                'D' => 'Timeliness',
            ],
            'correct' => 'C',
        ],
    ];

    /**
     * Compute Pre-Assessment score (number of 'Yes' responses: 0 to 6).
     */
    public static function computePreScore(array $answers): int
    {
        $score = 0;
        for ($i = 1; $i <= 6; $i++) {
            $key = "pre_q{$i}";
            if (isset($answers[$key]) && (bool)$answers[$key] === true) {
                $score++;
            }
        }
        return $score;
    }

    /**
     * Compute Post-Assessment score (number of correct answers: 0 to 6).
     */
    public static function computePostScore(array $answers): int
    {
        $score = 0;
        foreach (self::POST_QUESTIONS as $num => $meta) {
            $key = "post_q{$num}";
            if (isset($answers[$key]) && strtoupper(trim($answers[$key])) === $meta['correct']) {
                $score++;
            }
        }
        return $score;
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
        return $this->belongsTo(Participant::class)->withTrashed();
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
