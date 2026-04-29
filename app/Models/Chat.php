<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['appointment_id', 'message', 'is_read', 'chatable_type', 'chatable_id'];

    /**
     * Get the parent chatable model (doctor or patient).
     */
    public function chatable(): MorphTo
    {
        return $this->morphTo();
    }
}
