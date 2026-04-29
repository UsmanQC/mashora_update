<?php

namespace App\Models;
use App\Traits\Users;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use Users, SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'patients';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'id';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'birth_date', 'relation', 'gender', 'user_id','identity_id','identity_number'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'birth_date' => 'date',
        'gender' => 'integer',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['user_id', 'created_at', 'updated_at', 'appointments'];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function appointments(){
      return $this->hasMany(Appointment::class);
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['age_value', 'sex', 'appointments_count', 'profile_url', 'reports_count'];

    public function getAgeValueAttribute()
    {
        if(!is_null($this->birth_date)){
            $dateOfBirth = $this->birth_date;
            $today = date("Y-m-d");
            $diff = date_diff(date_create($dateOfBirth), date_create($today));

            return $diff->format('%y').' year';
        }
        return "";
    }

    public function getSexAttribute()
    {
        if($this->gender == 1){
            return "Male";
        }elseif($this->gender == 2){
            return "Female";
        }
        return "";
    }

    public function getProfileUrlAttribute()
    {
        if(!empty($this->user->profile_photo_url) && $this->id == $this->user->id){
            $path = $this->user->profile_photo_url ;
        }else{
            if($this->gender == 1)
                $path = asset('images/user-avatar.png') ;
            else
                $path = asset('images/woman-avatar.png');
        }
        $this->makeHidden('user');
        return $path ;
    }

    public function getAppointmentsCountAttribute()
    {
        return count($this->appointments);
    }

    public function getReportsCountAttribute()
    {
        $patient_id = $this->id;
        return AppointmentReport::whereIn('appointment_id', function($query) use($patient_id){
                    $query->select('id')
                    ->from('appointments')
                    ->where('patient_id', $patient_id);
                })->count();
    }
}
