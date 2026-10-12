<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TrainingModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_number',
        'code',
        'title',
        'description',
        'topics',
        'is_active',
    ];

    protected $casts = [
        'topics' => 'array',
        'is_active' => 'boolean',
        'module_number' => 'integer',
    ];

    public function batches(): BelongsToMany
    {
        return $this->belongsToMany(TrainingBatch::class, 'batch_modules')->withTimestamps();
    }
}
