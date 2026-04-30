<?php

namespace App\Models;

use App\Traits\Doctors;
use App\Traits\NotificationAble;
use App\Traits\RequiresSignature;
use Creagia\LaravelSignPad\Contracts\CanBeSigned;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Kyslik\ColumnSortable\Sortable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Doctor extends Authenticatable implements CanBeSigned, FilamentUser, HasMedia
{
    use Doctors, HasApiTokens, HasFactory, InteractsWithMedia, Notifiable, NotificationAble, RequiresSignature, SoftDeletes, Sortable;

    protected $guard = 'doctor';

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'doctor';
    }

    /**
     * Was: LaravelFCM HasPushToken — brozot/laravel-fcm does not support Laravel 13.
     * Use device tokens via NotificationAble / deviceTokens(), or install an FCM package compatible with your Laravel version.
     */
    public function routeNotificationForFCM(): ?string
    {
        return $this->attributes['push_token'] ?? null;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'name_ar',
        'spoken_languages',
        'email',
        'phone',
        'apple_id',
        'password',
        'verification_code',
        'profile_photo_path',
        'profile_detail_path',
        'registration_number',
        'gender',
        'degree_id',
        'speciality_id',
        'experience',
        'about',
        'about_ar',
        'profile_completed',
        'is_online',
        // 'device_token',
        'accept_instant_appointment',
        'status',
        'active_status',
        'commission',
        'medical_career_level',
        'signature',
    ];

    /**
     * The attributes that should be sortable for arrays.
     *
     * @var array
     */
    public $sortable = ['id', 'name', 'phone', 'status', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'degree_id' => 'integer',
        'speciality_id' => 'integer',
        'email_verified_at' => 'datetime',
        // 'gender' => 'integer',
        'experience' => 'integer',
        'type' => 'integer',
        'profile_completed' => 'boolean',
    ];

    public function routeNotificationForApn()
    {
        return $this->device_token;
    }

    /**
     * The durations that belong to the doctor.
     */
    public function durations(): BelongsToMany
    {
        return $this->belongsToMany(Duration::class, 'doctor_duration', 'doctor_id', 'duration')->withPivot('price');
    }

    /**
     * The communications that belong to the doctor.
     */
    public function communications(): BelongsToMany
    {
        return $this->belongsToMany(Communication::class, 'doctor_communication', 'doctor_id', 'communication');
    }

    /**
     * Get the bank account associated with the doctor.
     */
    public function bankAccount(): HasOne
    {
        return $this->hasOne(BankAccount::class);
    }

    /**
     * Get all of the appointment types for the doctor.
     */
    public function appointmentTypes(): HasMany
    {
        return $this->hasMany(DoctorCommunication::class);
    }

    /**
     * Get all of the working days for the doctor.
     */
    public function workingDays(): HasMany
    {
        return $this->hasMany(WorkingDay::class);
    }

    /**
     * Get all of the invoices for the doctor.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get all of the reviews for the doctor.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get all of the doctor's notifications.
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'userable');
    }

    /**
     * The patients that belong to the doctor.
     */
    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'doctor_patient', 'doctor_id', 'user_id')->withTimestamps();
    }

    /**
     * The associated specialities relationship for this doctor.
     */
    public function specialities(): BelongsToMany
    {
        return $this->belongsToMany(
            Speciality::class,
            'doctor_speciality',
            'doctor_id',
            'speciality_id'
        );
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'likes', 'doctor_id', 'user_id')->withTimestamps();
    }

    /**
     * Get of the user's Ratings.
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Get the degree of the doctor.
     */
    public function degree()
    {
        return $this->belongsTo(Degree::class)->withDefault([
            'title_locale' => '',
        ]);
    }

    /**
     * Get the appointments for the doctor.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * The associated assigned_appointments relationship for this service provider.
     */
    public function assigned_appointments(): BelongsToMany
    {
        return $this->belongsToMany(
            Appointment::class,
            'appointment_providers',
            'doctor_id',
            'appointment_id'
        );
    }

    /**
     * Get all of the user's contacts.
     */
    public function contacts()
    {
        return $this->morphMany(Contact::class, 'user');
    }

    /**
     * Get all of the post's comments.
     */
    public function chats(): MorphMany
    {
        return $this->morphMany(Chat::class, 'chatable');
    }

    /**
     * Set the user's default country code.
     *
     * @param  string  $value
     * @return void
     */
    public function setCountryCodeAttribute($value)
    {
        $this->attributes['country_code'] = (is_null($value) || $value == '') ? '+966' : $value;
    }

    public function getRecentReviewAttribute()
    {
        return $this->reviews()->with('user')->latest()->limit(3)->get();
    }

    public function getNameLocaleAttribute($value)
    {
        return \App::getLocale() == 'en' ? $this->name : $this->name_ar;
    }

    public function getPriceAttribute()
    {
        /*if(\Route::currentRouteName() == "services.doctors")
        {
            if(\Route::getCurrentRoute()->id == 23)//speciality of read laboratories results
                return 0;
            else
            {
                if($this->doctor_price > 0){
                    return $this->doctor_price;
                }elseif(!empty($this->degree)){
                    return $this->degree->price;
                }
            }
        }
        else
        {*/
        // if($this->doctor_price > 0){
        //     return $this->doctor_price;
        // }elseif(!empty($this->degree)){
        //     return $this->degree->price;
        // }
        // }
        return '40';
    }

    /**
     * This is for the Chatify //avatar
     */
    public function getAvatarAttribute()
    {
        return $this->profile_photo_path
                    ? Storage::url($this->profile_photo_path)
                    : $this->defaultProfilePhotoUrl();
    }

    /** profile_photo_url */
    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo_path
                    ? Storage::url($this->profile_photo_path)
                    : $this->defaultProfilePhotoUrl();
    }

    /** signature_url */
    public function getSignatureUrlAttribute()
    {
        return ! empty($this->sign) ? asset(Storage::url(optional($this->sign)->getSignatureImagePath())) : '';
    }

    public function getProfileDetailUrlAttribute()
    {
        return $this->profile_detail_path ? url($this->profile_detail_path) : '';
    }

    public function getExperienceYearAttribute()
    {
        if (! is_null($this->experience) && $this->experience != '') {
            return ($this->experience > 1) ? $this->experience.' years experience' : $this->experience.' year experience';
        }

        return '';
    }

    public function getEmailVerifiedAttribute()
    {
        return is_null($this->email_verified_at)
                    ? false
                    : true;
    }

    // public function getOtherSpecialitiesAttribute()
    // {
    //     return $this->specialities()->get()->pluck('id')->toArray();
    // }

    // public function getSpecialitiesAttribute()
    // {
    //     return $this->specialities()->get();
    // }

    public function getDegreeTitleAttribute()
    {
        return optional($this->degree)->title_locale;
    }

    // public function getSpecialityTitleAttribute()
    // {
    //     return !empty($this->speciality)?(\App::getLocale() == 'en' ? $this->speciality->title : $this->speciality->title_ar):'';
    // }

    public function getSexAttribute()
    {
        if (\App::getLocale() == 'en') {
            if ($this->gender == 'male') {
                return 'Male';
            } elseif ($this->gender == 'female') {
                return 'Female';
            }
        } else {
            if ($this->gender == 'male') {
                return 'ذكر';
            } elseif ($this->gender == 'female') {
                return 'أنثى';
            }
        }

        return '';
    }

    public function getAverageRatingAttribute()
    {
        return (int) $this->reviews()->average('rating');
    }

    public function getAboutLocaleAttribute($value)
    {
        return \App::getLocale() == 'en' ? $this->about : $this->about_ar;
    }

    // public function getTimeCallAttribute()
    // {
    //     $time_call = 25;
    //     $doctor_speciality = $this->speciality;
    //     if($doctor_speciality)
    //         $time_call = $doctor_speciality->total_time_call ? $doctor_speciality->total_time_call : $time_call;
    //     if(\Route::currentRouteName() == "services.doctors")
    //     {
    //         if($doctor_speciality->id == \Route::getCurrentRoute()->id)
    //             $time_call = $doctor_speciality->total_time_call ? $doctor_speciality->total_time_call : $time_call;
    //         elseif(\Route::getCurrentRoute()->id != 0 )
    //         {
    //             $speciality = $this->specialities->where('id',\Route::getCurrentRoute()->id)->first();
    //             $time_call = $speciality->total_time_call ? $speciality->total_time_call : $time_call;
    //         }
    //     }
    //     return $time_call;
    // }

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     *
     * @return string
     */
    protected function defaultProfilePhotoUrl()
    {
        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=3C5CF7&background=C3CEFF';
    }

    /**
     * Get of the Doctor's favoris.
     */
    public function favoris()
    {
        return $this->hasMany(Favori::class);
    }

    /**
     * save device tokens
     */
    public function deviceTokens()
    {
        return $this->morphMany(DeviceToken::class, 'userable');
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query): void
    {
        $query->where('is_online', 1)->where('status', 'approved');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('medical_license');
    }
}
