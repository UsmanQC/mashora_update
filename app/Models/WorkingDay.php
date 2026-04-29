<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkingDay extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['doctor_id', 'day_of_week', 'override_date', 'is_working'];

        /**
     * Get all of the Working Hours for the available day.
     */
    public function workingHours(): HasMany
    {
        return $this->hasMany(WorkingHour::class);
    }
}
