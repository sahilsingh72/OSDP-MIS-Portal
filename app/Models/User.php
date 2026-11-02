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
    use HasFactory, Notifiable;
    use HasRoles {
        assignRole as protected traitAssignRole;
        syncRoles as protected traitSyncRoles;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'name',
        'email',
        'phone',
        'designation',
        'department',
        'district_id',
        'block_id',
        'gram_panchayat_id',
        'is_active',
        'password',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * Assign the given role to the model and sync role_id column.
     */
    public function assignRole(...$roles)
    {
        $result = $this->traitAssignRole(...$roles);
        $firstRole = $this->roles()->first();
        if ($firstRole && $this->role_id !== $firstRole->id) {
            $this->role_id = $firstRole->id;
            $this->saveQuietly();
        }
        return $result;
    }

    /**
     * Remove all existing roles and set the given role, syncing role_id column.
     */
    public function syncRoles(...$roles)
    {
        $result = $this->traitSyncRoles(...$roles);
        $firstRole = $this->roles()->first();
        $newRoleId = $firstRole ? $firstRole->id : null;
        if ($this->role_id !== $newRoleId) {
            $this->role_id = $newRoleId;
            $this->saveQuietly();
        }
        return $result;
    }

    /**
     * Primary assigned system role.
     */
    public function primaryRole(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_id');
    }

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
     * Home district for Field Trainer / Staff
     */
    public function district(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Home block for Field Trainer / Staff
     */
    public function block(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    /**
     * Home Gram Panchayat for Field Trainer / Staff
     */
    public function gramPanchayat(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GramPanchayat::class);
    }

    /**
     * Dedicated Field Trainer profile record if this user is a trainer.
     */
    public function fieldTrainer(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(FieldTrainer::class, 'user_id');
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
