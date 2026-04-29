<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notified extends Model
{
    use HasFactory;

    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
        'type'
    ];

    /**
     * Notifiable morphs
     *
     */
    public function notifiable()
    {
        return $this->morphTo();
    }
}
