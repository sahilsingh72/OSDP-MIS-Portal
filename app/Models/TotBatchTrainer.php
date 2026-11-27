<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TotBatchTrainer extends Model
{
    use HasFactory;

    protected $table = 'tot_batch_trainers';

    protected $fillable = [
        'tot_batch_id',
        'field_trainer_id',
        'is_present',
        'remarks',
        'marked_by',
        'marked_at',
    ];

    protected $casts = [
        'is_present' => 'boolean',
        'marked_at' => 'datetime',
    ];

    public function totBatch(): BelongsTo
    {
        return $this->belongsTo(TotBatch::class, 'tot_batch_id');
    }

    public function fieldTrainer(): BelongsTo
    {
        return $this->belongsTo(FieldTrainer::class, 'field_trainer_id');
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
