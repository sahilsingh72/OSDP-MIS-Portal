<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Participant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'designation',
        'department',
        'office_name',
        'phone_masked',
        'phone',
        'email',
        'is_active',
        'created_by',
    ];

    protected $appends = [
        'phone',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Accessor for unmasked phone number.
     */
    public function getPhoneAttribute(): ?string
    {
        return $this->phone_masked;
    }

    /**
     * Mutator for phone number.
     */
    public function setPhoneAttribute(?string $value): void
    {
        $this->attributes['phone_masked'] = $value ? trim($value) : null;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function batchParticipants(): HasMany
    {
        return $this->hasMany(BatchParticipant::class);
    }

    public function batches(): BelongsToMany
    {
        return $this->belongsToMany(TrainingBatch::class, 'batch_participants')->withTimestamps();
    }
}
