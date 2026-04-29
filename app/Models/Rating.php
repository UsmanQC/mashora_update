<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rating extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ratings';

    /**
     * The database primary key value.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'doctor_id', 'appointment_id','rating_question_id','user_id','value',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * doctor responsable
     */
    public function doctor()
    {
        return $this->belongsTo('App\Models\Doctor') ;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * use  create
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * appointment between user and doctor
     */
 public function appointment()
    {
        return $this->belongsTo('App\Models\Appointment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * question in rating
     */
 public function ratingquestion()
    {
        return $this->belongsTo('App\Models\RatingQuestion' , 'rating_question_id');
    }


}
