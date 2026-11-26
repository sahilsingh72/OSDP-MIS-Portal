<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_batch_id',
        'batch_participant_id',
        'is_present',
        'morning_present',
        'afternoon_present',
        'remarks',
        'marked_by',
    ];

    protected $casts = [
        'is_present' => 'boolean',
        'morning_present' => 'boolean',
        'afternoon_present' => 'boolean',
    ];

    public function getIsPresentAttribute($value): bool
    {
        if ($value !== null) {
            return (bool) $value;
        }
        return (bool) ($this->morning_present || $this->afternoon_present);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(TrainingBatch::class, 'training_batch_id');
    }

    public function batchParticipant(): BelongsTo
    {
        return $this->belongsTo(BatchParticipant::class);
    }

    public function marker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
