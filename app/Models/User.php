<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'designation',
        'department',
        'is_active',
        'password',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Assigned districts for District Coordinators & Monitors
     */
    public function assignedDistricts(): BelongsToMany
    {
        return $this->belongsToMany(District::class, 'user_districts')->withTimestamps();
    }

    /**
     * Batches assigned to this trainer
     */
    public function trainerBatches(): BelongsToMany
    {
        return $this->belongsToMany(TrainingBatch::class, 'batch_trainers')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Batches created by this user
     */
    public function createdBatches(): HasMany
    {
        return $this->hasMany(TrainingBatch::class, 'created_by');
    }

    /**
     * Reviews submitted by this user
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(BatchReview::class, 'reviewer_id');
    }

    /**
     * Field verifications conducted by this monitor
     */
    public function fieldVerifications(): HasMany
    {
        return $this->hasMany(FieldVerification::class, 'monitor_id');
    }

    /**
     * Check if user is assigned to a specific district
     */
    public function isAssignedToDistrict(int $districtId): bool
    {
        return $this->assignedDistricts()->where('districts.id', $districtId)->exists();
    }

    /**
     * Check if user is assigned as trainer to a specific batch
     */
    public function isAssignedToBatch(int $batchId): bool
    {
        return $this->trainerBatches()->where('training_batches.id', $batchId)->exists();
    }
}
