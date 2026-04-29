<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Exam extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['uuid', 'title', 'title_ar', 'estimate_time', 'image_path', 'order', 'enabled'];

    /**
     * The questions that belong to the exam.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Get the results for the user.
     */
    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public function getTitleLocaleAttribute($value)
    {
        return \App::getLocale() == 'en' ? $this->title : $this->title_ar;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($exam) {
            $exam->uuid = (string) Str::uuid();
        });
    }
}
