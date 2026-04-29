<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PatientMood extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'mood', 'comments', 'date', 'is_shared'];

/**
     * Get the user that owns the mood.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
