<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\UserTypeEnum;

class VerifyPhoneNumber extends Model
{
    use HasFactory;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'phone',
        'verification_code',
        'user_type',
    ];

    /**
     * Write code on Method
     *
     * @return response()
     */
    protected $casts = [
        'user_type' => UserTypeEnum::class
    ];
}
