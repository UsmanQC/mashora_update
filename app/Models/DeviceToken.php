<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    use HasFactory;

    protected $table = "device_tokens";

    protected $fillable = [
        'userable_id',
        'userable_type',
        'device_token'
    ];

    public function userable()
    {
        return $this->morphTo();
    }
}
