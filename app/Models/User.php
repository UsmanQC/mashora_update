<?php

namespace App\Models;

use App\Traits\NotificationAble;
use App\Traits\Users;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Kyslik\ColumnSortable\Sortable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use Users;
    use Sortable;
    use NotificationAble;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'verification_code',
        'profile_photo_path',
        'gender',
        'birth_date',
        'consultation',
        // 'identity_id',
        // 'identity_number',
        // 'device_token',
        'profile_completed',
        'status',
    ];

    /**
     * The attributes that should be sortable for arrays.
     *
     * @var array
     */
    public $sortable = ['id', 'phone', 'created_at', 'updated_at'];

    public function getNameLocaleAttribute($value)
    {
        return \App::getLocale() == 'en' ? $this->name : $this->name_ar;
    }

   /**
     * This is for the Chatify //avatar
     *
     */
    public function getAvatarAttribute()
    {
        return $this->profile_photo_path
                    ?  Storage::url($this->profile_photo_path)
                    : $this->defaultProfilePhotoUrl();
    }

    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo_path
                    ?  Storage::url($this->profile_photo_path)
                    : $this->defaultProfilePhotoUrl();
    }

    public function likedDoctors()
    {
        return $this->belongsToMany(Doctor::class, 'likes', 'user_id', 'doctor_id')->withTimestamps();
    }

    /**
     * Get the patient moods for the user.
     */
    public function patientMoods(): HasMany
    {
        return $this->hasMany(PatientMood::class);
    }

    /**
     * Get of the user's patients.
     */
    public function patients()
    {
        return $this->hasMany(Patient::class);
    }

    /**
     * Get of the user's appointments.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the today appointments // today_appointments_count
     *
     */
    public function getTodayAppointmentsCountAttribute()
    {
        return $this->appointments->where('appointment_date', now()->format('Y-m-d'))->count();
    }

    /**
     * Get all of the doctor's notifications.
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'userable');
    }

    /**
     * Get of the user's Ratings.
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Get of the user's reviews.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the results for the user.
     */
    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    /**
     * Get all of the post's comments.
     */
    public function chats(): MorphMany
    {
        return $this->morphMany(Chat::class, 'chatable');
    }

    /**
     * save device tokens
     */
    public function deviceTokens()
    {
        return $this->morphMany(DeviceToken::class, 'userable');
    }

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     *
     * @return string
     */
    protected function defaultProfilePhotoUrl()
    {
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=3C5CF7&background=C3CEFF';
    }
}
