<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_name',
        'rating', //Average review
        'commitment', //How was the specialist’s commitment to attendance?
        'interaction', //How was the specialist’s interaction and empathy?
        'behavior', //How was the specialist’s behavior?
        'environment', //Was the environment appropriate and quiet?
        'listening', //Was the specialist a good listener?
        'comment',
        'appointment_id',
        'doctor_id',
        'user_id'
    ];

    /**
     * Get the doctor that owns the review.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the user that owns the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the appointment that owns the review.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function getStatusAttribute()
    {
        if ($this->rating === 1) {
            return __('locale.bad');
        } elseif ($this->rating >= 2 && $this->rating <= 3) {
            return __('locale.average');
        } elseif ($this->rating === 4) {
            return __('locale.good');
        } elseif ($this->rating === 5) {
            return __('locale.excellent');
        } else {
            // Handle other cases or return a default status
            return 'unknown';
        }
    }
}
