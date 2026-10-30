<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingBatch extends Model
{
    use HasFactory, SoftDeletes;

    // Allowed status values
    public const STATUS_PLANNED = 'planned';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_AWAITING_EVIDENCE = 'awaiting_evidence';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_CORRECTION_REQUIRED = 'correction_required';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'batch_code',
        'training_plan_id',
        'training_tier_id',
        'district_id',
        'block_id',
        'venue_id',
        'scheduled_date',
        'start_time',
        'end_time',
        'actual_start_time',
        'actual_end_time',
        'status',
        'planned_participants',
        'registered_participants',
        'present_participants',
        'submission_deadline',
        'submitted_at',
        'verified_at',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'submission_deadline' => 'datetime',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'planned_participants' => 'integer',
        'registered_participants' => 'integer',
        'present_participants' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(TrainingPlan::class, 'training_plan_id');
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(TrainingTier::class, 'training_tier_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function trainers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'batch_trainers')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function clusterGps(): BelongsToMany
    {
        return $this->belongsToMany(GramPanchayat::class, 'batch_gram_panchayats')->withTimestamps();
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(TrainingModule::class, 'batch_modules')->withTimestamps();
    }

    public function batchParticipants(): HasMany
    {
        return $this->hasMany(BatchParticipant::class);
    }

    public function activeBatchParticipants(): HasMany
    {
        return $this->hasMany(BatchParticipant::class)->whereHas('participant', function ($q) {
            $q->whereNull('deleted_at');
        });
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function uploads(): HasMany
    {
        return $this->hasMany(TrainingUpload::class)->where('is_valid', true);
    }

    public function allUploads(): HasMany
    {
        return $this->hasMany(TrainingUpload::class);
    }

    public function summary(): HasOne
    {
        return $this->hasOne(BatchSummary::class);
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(BatchFeedback::class);
    }

    public function participantFeedback(): HasMany
    {
        return $this->hasMany(ParticipantFeedback::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(BatchReview::class)->latest();
    }

    public function fieldVerifications(): HasMany
    {
        return $this->hasMany(FieldVerification::class);
    }

    /**
     * Check evidence completeness (all 6 items)
     */
    public function getEvidenceStatusAttribute(): array
    {
        $types = $this->uploads()->pluck('evidence_type')->toArray();
        $required = [
            'attendance_sheet' => in_array('attendance_sheet', $types),
            'photo_1' => in_array('photo_1', $types),
            'photo_2' => in_array('photo_2', $types),
            'video' => in_array('video', $types),
            'feedback_forms' => in_array('feedback_forms', $types),
            'batch_completion_report' => in_array('batch_completion_report', $types) || $this->summary()->exists(),
        ];

        $completedCount = count(array_filter($required));
        return [
            'items' => $required,
            'completed_count' => $completedCount,
            'total_required' => 6,
            'is_complete' => $completedCount === 6,
        ];
    }

    /**
     * Check if batch submission is overdue (> 48 hours without submission)
     */
    public function getIsOverdueAttribute(): bool
    {
        if (in_array($this->status, [self::STATUS_VERIFIED, self::STATUS_CLOSED, self::STATUS_SUBMITTED])) {
            return false;
        }

        if ($this->submission_deadline && Carbon::now()->greaterThan($this->submission_deadline)) {
            return true;
        }

        return false;
    }

    /**
     * Scope query to authenticated user's administrative scope
     */
    public function scopeForUserScope(Builder $query, User $user): Builder
    {
        if ($user->hasRole(['super-admin', 'ocac', 'pmu'])) {
            return $query;
        }

        if ($user->hasRole('district-coordinator')) {
            $assignedDistrictIds = $user->assignedDistricts()->pluck('districts.id')->toArray();
            return $query->whereIn('district_id', $assignedDistrictIds);
        }

        if ($user->hasRole('field-trainer')) {
            return $query->whereHas('trainers', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        if ($user->hasRole('field-monitor')) {
            $assignedDistrictIds = $user->assignedDistricts()->pluck('districts.id')->toArray();

            return $query->where(function ($sub) use ($user, $assignedDistrictIds) {
                $sub->whereHas('fieldVerifications', function ($q) use ($user) {
                    $q->where('monitor_id', $user->id);
                });

                if (!empty($assignedDistrictIds)) {
                    $sub->orWhereIn('district_id', $assignedDistrictIds);
                }
            });
        }

        return $query->whereRaw('1 = 0'); // Deny by default
    }

    /**
     * Recalculate registered and present counts excluding soft-deleted participants.
     */
    public function recalculateCounts(): void
    {
        $registered = $this->batchParticipants()
            ->whereHas('participant', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->count();

        $present = $this->attendanceRecords()
            ->whereHas('batchParticipant.participant', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->where(function ($q) {
                $q->where('morning_present', true)->orWhere('afternoon_present', true);
            })
            ->count();

        $this->update([
            'registered_participants' => $registered,
            'present_participants' => $present,
        ]);
    }
}

