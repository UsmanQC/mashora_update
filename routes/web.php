<?php

use App\Http\Controllers\Doctor\DoctorSignPadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['web', 'auth:doctor'])->post('/doctor/sign-pad', DoctorSignPadController::class)
    ->name('doctor.sign-pad.store');
