<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingUpload extends Model
{
    use HasFactory, SoftDeletes;

    // The 6 evidence types
    public const TYPE_ATTENDANCE = 'attendance_sheet';
    public const TYPE_PHOTO_1 = 'photo_1';
    public const TYPE_PHOTO_2 = 'photo_2';
    public const TYPE_VIDEO = 'video';
    public const TYPE_FEEDBACK = 'feedback_forms';
    public const TYPE_COMPLETION_REPORT = 'batch_completion_report';

    protected $fillable = [
        'training_batch_id',
        'evidence_type',
        'original_filename',
        'file_path',
        'mime_type',
        'file_size_bytes',
        'file_hash',
        'uploaded_by',
        'version',
        'is_valid',
        'remarks',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'file_size_bytes' => 'integer',
        'version' => 'integer',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(TrainingBatch::class, 'training_batch_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Human-friendly label for evidence item
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->evidence_type) {
            self::TYPE_ATTENDANCE => 'Attendance Sheet (Form 2)',
            self::TYPE_PHOTO_1 => 'Photo 1 (Group with Banner)',
            self::TYPE_PHOTO_2 => 'Photo 2 (Trainer Teaching)',
            self::TYPE_VIDEO => 'Short Video Clip (MP4)',
            self::TYPE_FEEDBACK => 'Feedback Forms (Form 1)',
            self::TYPE_COMPLETION_REPORT => 'Batch Completion Report (Form 3)',
            default => ucfirst(str_replace('_', ' ', $this->evidence_type)),
        };
    }

    /**
     * File size formatted in KB / MB
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size_bytes;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        return number_format($bytes / 1024, 1) . ' KB';
    }
}
