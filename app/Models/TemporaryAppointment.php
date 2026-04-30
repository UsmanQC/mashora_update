<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemporaryAppointment extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['doctor_id', 'user_id', 'appointment_id', 'scheduled_at', 'appointment_date', 'start_time', 'end_time', 'duration', 'extend_at', 'patient_name', 'patient_email', 'patient_phone', 'appointment_for', 'patient_notes', 'communications', 'invoice_id', 'invoice_url', 'payment_response', 'payment_status', 'amount', 'discount', 'tax', 'total', 'appointment_type', 'instant_counseling', 'payment_session_id', 'payment_invoice_id', 'payment_invoice_url'];

    /**
     * Get the doctor that owns the TemporaryAppointment.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Interact with the communications.
     */
    protected function communications(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value),
            set: fn ($value) => json_encode($value),
        );
    }

    /**
     * Interact with the instant counseling.
     */
    protected function instantCounseling(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
            set: fn ($value) => json_encode($value),
        );
    }

    public function scopeAvailableForFiveMin($query, $doctorId, $startTime, $endTime, $date = null)
    {
        $date = $date ?: now()->format('Y-m-d');

        return $query->where(function ($subquery) use ($startTime, $endTime) {
            $subquery->where(function ($subsubquery) use ($startTime, $endTime) {
                $subsubquery->where('start_time', '>=', $startTime)
                    ->where('start_time', '<', $endTime);
            })
                ->orWhere(function ($subsubquery) use ($startTime, $endTime) {
                    $subsubquery->where('end_time', '>', $startTime)
                        ->where('end_time', '<=', $endTime);
                })
                ->orWhere(function ($subsubquery) use ($startTime, $endTime) {
                    $subsubquery->where('start_time', '<', $startTime)
                        ->where('end_time', '>', $endTime);
                });
        })
            ->where('user_id', '!=', auth()->user()->id)
            ->where('doctor_id', $doctorId)
            ->where('appointment_date', $date)
            ->where('created_at', '>=', Carbon::now()->subMinutes(5));
    }
}
