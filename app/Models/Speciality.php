<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Speciality extends Model
{
    use Sortable, SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'specialities';

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
    protected $fillable = [
        'title',
        'title_ar',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'status',
        // 'icon_path',
        // 'image_path',
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be sortable for arrays.
     *
     * @var array
     */
    public $sortable = ['id', 'title', 'title_ar', 'status', 'created_at', 'updated_at'];

    /*public function therapies() {
        return $this->hasMany('App\Models\Therapy');
    }*/

    /**
     * @return BelongsToMany
     */
    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(
            Doctor::class,
            'doctor_speciality',
            'speciality_id',
            'doctor_id'
        );
    }

    /**
     * Attributes
     */
    // protected $appends = ['image_url' , 'icon_url'];

    public function getImageUrlAttribute()
    {
        return $this->image_path
                    ? url($this->image_path)
                    : $this->defaultPhotoUrl();
    }

    public function getTitleLocaleAttribute($value)
    {
        return \App::getLocale() == 'en' ? $this->title : $this->title_ar;
    }

    public function getDescriptionAttribute($value)
    {
        return \App::getLocale() == 'en' ? $value : $this->description_ar;
    }

    /**
     * Attributes
     */
    // public function getIconUrlAttribute()
    // {
    //     return $this->icon_path
    //                 ? url($this->icon_path)
    //                 : $this->defaultPhotoUrl();
    // }

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     *
     * @return string
     */
    protected function defaultPhotoUrl()
    {
        return 'https://ui-avatars.com/api/?name='.urlencode($this->title).'&color=7F9CF5&background=EBF4FF';
    }
}
