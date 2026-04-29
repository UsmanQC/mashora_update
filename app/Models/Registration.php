<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'registrations';

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
    protected $fillable = ['project_id', 'voucher_id', 'user_id', 'project_title', 'voucher_title', 'consent', 'patient_phone', 'patient_id_number', 'patient_city'];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
    
}
