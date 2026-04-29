<?php

namespace App\Models;

use App\Traits\Resultable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Result extends Model
{
    use HasFactory, Resultable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['uuid', 'exam_id', 'user_id', 'score', 'finished'];


    /**
     * Get the questions for the result.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(ResultQuestion::class);
    }

    /**
     * Get the exam that owns the result.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($resut) {
            $resut->uuid = (string) Str::uuid();
        });
    }
}
