<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_batch_id',
        'monitor_id',
        'visit_date',
        'session_in_progress',
        'verified_headcount',
        'banner_displayed',
        'kits_distributed',
        'tea_lunch_provided',
        'training_quality_rating',
        'observations',
        'corrective_actions_needed',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'session_in_progress' => 'boolean',
        'banner_displayed' => 'boolean',
        'kits_distributed' => 'boolean',
        'tea_lunch_provided' => 'boolean',
        'verified_headcount' => 'integer',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(TrainingBatch::class, 'training_batch_id');
    }

    public function monitor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'monitor_id');
    }
}
