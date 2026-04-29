<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Coupon extends Model
{
    use Sortable, SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'coupons';

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
    protected $fillable = ['coupon_name', 'coupon_description', 'coupon_code', 'coupon_type', 'discount', 'starts_at', 'ends_at', 'status','type','service_id'];

    /**
     * The attributes that should be sortable for arrays.
     *
     * @var array
     */
    public $sortable = ['id', 'coupon_name', 'status', 'created_at', 'updated_at'];

    /**
     * Scope a query to only active coupon.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1)->where('starts_at' , '<=', Carbon::now()->toDateString())
                                ->where('ends_at' , '>=', Carbon::now()->toDateString());
    }

    /**
     * service
     */
    public function service()
    {
        if($this->type == 'telemedicine')
            return $this->belongsTo('App\Models\Speciality');
        else
            return $this->belongsTo('App\Models\HomeCareService');
    }
}
