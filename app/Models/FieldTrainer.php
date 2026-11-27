<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FieldTrainer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'field_trainers';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'designation',
        'department',
        'district_id',
        'block_id',
        'gram_panchayat_id',
        'is_active',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The linked login user account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The assigned home district.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * The assigned home block.
     */
    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    /**
     * The assigned home Gram Panchayat.
     */
    public function gramPanchayat(): BelongsTo
    {
        return $this->belongsTo(GramPanchayat::class);
    }

    /**
     * The user (DLC/PMU) who created this trainer profile.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * TOT batch enrollment & 1-day attendance records for this trainer.
     */
    public function totEnrollments(): HasMany
    {
        return $this->hasMany(TotBatchTrainer::class, 'field_trainer_id');
    }

    /**
     * TOT batches attended/enrolled by this trainer.
     */
    public function totBatches(): BelongsToMany
    {
        return $this->belongsToMany(TotBatch::class, 'tot_batch_trainers')
            ->withPivot(['id', 'is_present', 'remarks', 'marked_by', 'marked_at'])
            ->withTimestamps();
    }
}
