<?php

namespace App\Enums;

enum UserTypeEnum:string {
    case Doctor = 'doctor';
    case Patient = 'patient';
}
