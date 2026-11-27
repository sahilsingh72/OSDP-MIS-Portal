<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TotBatch extends Model
{
    use HasFactory;

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'batch_code',
        'title',
        'training_date',
        'tot_venue_id',
        'venue_name',
        'venue_address',
        'district_id',
        'status',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'training_date' => 'date',
    ];

    public function totVenue(): BelongsTo
    {
        return $this->belongsTo(TotVenue::class, 'tot_venue_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(TotBatchTrainer::class);
    }

    public function trainers(): BelongsToMany
    {
        return $this->belongsToMany(FieldTrainer::class, 'tot_batch_trainers')
            ->withPivot(['id', 'is_present', 'remarks', 'marked_by', 'marked_at'])
            ->withTimestamps();
    }
}
