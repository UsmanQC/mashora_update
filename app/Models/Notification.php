<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'notifications';

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
    protected $fillable = ['type', 'title', 'message', 'read_at', 'notifiable_id', 'notifiable_type', 'senderable_id', 'senderable_type', 'userable_type', 'userable_id', 'action'];

    /**
     * Get the parent notifiable model
     *
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the parent userable model
     *
     */
    public function userable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the parent senderable model (doctor or patient).
     *
     */
    public function senderable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getNotificationAtAttribute()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }
}
