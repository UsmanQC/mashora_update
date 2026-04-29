<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SickLeaves extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sick_leaves';

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
    protected $fillable = ['appointment_id', 'diagnosis', 'sick_leave', 'start_date', 'end_date','sick_leave_pdf'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];

    public function getSickLeavePdfAttribute($value)
    {
        if($value && $value != ""){
            return asset('storage/'.$value);              
        }
        return "";        
    }

    public function appointment()
    {
        return $this->belongsTo('App\Models\Appointment');
    }  
}
