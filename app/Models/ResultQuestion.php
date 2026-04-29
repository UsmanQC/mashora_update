<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ResultQuestion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['result_id', 'question_id', 'option_id', 'value'];

    /**
     * Get the result for the question.
     */
    public function result(): BelongsTo
    {
        return $this->belongsTo(Result::class);
    }
}
