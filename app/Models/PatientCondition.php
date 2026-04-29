<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientCondition extends Model
{
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'patient_conditions';

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
    protected $fillable = ['id', 'title_ar', 'title_en' ,"path_blue_grey" ,"path_blue","path_grey","path_white" , "created_at" , "updated_at"];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [ "created_at" , "updated_at"];


       /**
     * get Image  path_blue_grey
     */
    public function getPathBlueGreyAttribute($path_blue_grey)
    {

            return asset('upload/blue_grey_icons/'.$path_blue_grey);

    }


    /**
     * get Image  path_blue
     */
    public function getPathBlueAttribute($path_blue)
    {

            return asset('upload/blue_icons/'.$path_blue);

    }

    /**
     * get Image  path_grey
     */
    public function getPathGreyAttribute($path_grey)
    {

            return asset('upload/grey_icons/'.$path_grey);

    }

    /**
     * get Image  path_white
     */
    public function getPathWhiteAttribute($path_white)
    {
                    return asset('upload/white_icons/'.$path_white);

    }


}
