<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['uuid', 'exam_id', 'title', 'title_ar', 'order'];

    /**
     * The options that belong to the question.
     */
    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function getTitleLocaleAttribute($value)
    {
        return \App::getLocale() == 'en' ? $this->title : $this->title_ar;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($question) {
            $question->uuid = (string) Str::uuid();
        });
    }
}
