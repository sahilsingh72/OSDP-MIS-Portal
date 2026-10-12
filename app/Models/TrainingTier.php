<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'target_duration_hours',
        'target_batch_size',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'target_duration_hours' => 'decimal:1',
        'target_batch_size' => 'integer',
    ];

    public function batches(): HasMany
    {
        return $this->hasMany(TrainingBatch::class);
    }
}
