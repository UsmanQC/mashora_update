<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Appointment extends Model
{
    use Sortable, SoftDeletes, HasFactory;

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['appointment_number', 'doctor_id', 'user_id', 'scheduled_at', 'appointment_date', 'start_time', 'end_time', 'duration', 'actual_start_at', 'actual_end_at', 'extend_at', 'patient_name', 'patient_email', 'patient_phone', 'appointment_for', 'patient_notes', 'doctor_notes', 'appointment_type', 'instant_counseling', 'status', 'amount', 'discount', 'tax', 'total', 'doctor_share', 'mashora_share', 'payment_session_id', 'payment_invoice_id', 'payment_invoice_url', 'refund_payment_invoice_id', 'refund_payment_response', 'extend_duration', 'prescription_not_needed', 'cancel_status'];

    protected $casts = [
        'appointment_date' => 'date',
    ];
    /**
     * Get the doctor that owns the appointment.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the user that owns the appointment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the medications for the appointment.
     */
    public function medications(): HasMany
    {
        return $this->hasMany(Medication::class);
    }

    /**
     * Get the reviews for the blog post.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the diagnosis associated with the appointment.
     */
    public function diagnosis(): HasOne
    {
        return $this->hasOne(Diagnosis::class);
    }

    /**
     * Get of the user's Ratings.
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * The communications that belong to the doctor.
     */
    public function communications(): BelongsToMany
    {
        return $this->belongsToMany(Communication::class, 'appointment_communication', 'appointment_id', 'communication');
    }


    /**
     * Get all of the Preferred Communication Methods for patient.
     */
    public function preferredCommunications(): HasMany
    {
        return $this->hasMany(AppointmentCommunication::class);
    }

    /**
     * Get the temporary appointment.
     */
    public function temporaryAppointment(): HasOne
    {
        return $this->hasOne(TemporaryAppointment::class);
    }

    // public function reports(){
    //   return $this->hasMany(AppointmentReport::class);
    // }

    // public function appointmentTests(){
    //   return $this->hasMany(AppointmentTests::class);
    // }

    // public function tests(){
    //   return $this->belongsToMany(Test::class,'appointment_tests','appointment_id','test_id');
    // }

    public function state()
    {
        return $this->belongsTo('App\Models\State');
    }

    // public function testsAppointment(){
    //   return $this->belongsToMany(Test::class,'appointment_tests','appointment_id','test_id');
    // }

    public function appointmentPackage()
    {
        return $this->belongsTo('App\Models\Package');
    }

    public function sick_leave()
    {
        return $this->hasOne(SickLeaves::class);
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    /**
     * Get notifieds
     *
     */
    public function notifieds()
    {
        return $this->morphMany(Notified::class, 'notifiable');
    }

    /**
     * Get chat messages for the appointment
     *
     */
    public function ch_messages(){
        return $this->hasMany(ChMessage::class, 'appointment_id', 'id');
    }

    /**
     * @return BelongsToMany
     */
    public function assigned_doctors(): BelongsToMany
    {
        return $this->belongsToMany(
            Doctor::class,
            'appointment_providers',
            'appointment_id',
            'doctor_id'
        );
    }

    /**
     * Set the order number.
     *
     * @param  string  $value
     * @return void
     */
    public static function getAppointmentNumber()
    {
        $appointmentNumber = 'APP' . date('Ymd') . Appointment::generateRandomString(6);
        $appointmentNumber = (Appointment::where('appointment_number', $appointmentNumber)->exists()) ? Appointment::getAppointmentNumber() : $appointmentNumber;
        return $appointmentNumber;
    }

    /**
     * Get the appointment_cancel_status
     */
    public function getAppointmentCancelStatusAttribute()
    {
        $cancelStatus = null;

        switch ($this->cancel_status) {
            case 'cancel':
                $cancelStatus = '';
                break;

            case 'manually':
                $cancelStatus = '(Refund Manually)';
                break;

            case 'automatic':
                $cancelStatus = '(Refund Automatic)';
                break; 
        }
        return $cancelStatus;
    }

    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function scopeAvailableFor($query, $doctorId, $startTime, $endTime, $date = null)
    {
        $date = $date ?: now()->format('Y-m-d');
        return $query->where(function ($subquery) use ($startTime, $endTime, $date) {
            $subquery->where(function ($subsubquery) use ($startTime, $endTime, $date) {
                $subsubquery->where('start_time', '>=', $startTime)
                    ->where('start_time', '<', $endTime);
            })
                ->orWhere(function ($subsubquery) use ($startTime, $endTime, $date) {
                    $subsubquery->where('end_time', '>', $startTime)
                        ->where('end_time', '<=', $endTime);
                })
                ->orWhere(function ($subsubquery) use ($startTime, $endTime, $date) {
                    $subsubquery->where('start_time', '<', $startTime)
                        ->where('end_time', '>', $endTime);
                });
        })
            ->where('doctor_id', $doctorId)
            ->where('appointment_date', $date);
    }
}
