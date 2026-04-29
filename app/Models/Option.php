<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'title_ar', 'value', 'order'];

    /**
     * Get the question that wrote the option.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function getTitleLocaleAttribute($value)
    {
        return \App::getLocale() == 'en' ? $this->title : $this->title_ar;
    }
}
