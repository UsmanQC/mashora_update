<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\SoftDeletes;

class RatingQuestion extends Model
{
    use Sortable, SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rating_questions';

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
        'id', 'question' ,'question_ar' ,'tags_ar' ,'tags' , "created_at" , "updated_at"
    ];
 /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [ "created_at" , "updated_at"];


    /**
     * Get of the user's Ratings.
     */
    public function ratings() {
        return $this->hasMany(Rating::class);
    }

}
