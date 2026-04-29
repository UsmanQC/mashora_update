<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Reason  extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'reasons';

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
    protected $fillable = ['id', 'title_ar', 'title_en' , "created_at" , "updated_at"];
 /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [ "created_at" , "updated_at"];



 /**
     * Get of the user's Ratings.
     */
    public function moodsPatientReasons() {
        return $this->hasMany(MoodsPatientReason::class);
    }



}
