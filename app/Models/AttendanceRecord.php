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
        'morning_present',
        'afternoon_present',
        'remarks',
        'marked_by',
    ];

    protected $casts = [
        'morning_present' => 'boolean',
        'afternoon_present' => 'boolean',
    ];

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
