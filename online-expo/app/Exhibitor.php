<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exhibitor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'exhibition_zone_id',
        'zip_code1',
        'zip_code2',
        'prefecture_id',
        'address',
        'building_name',
        'tel',
        'url',
        'profile_text',
    ];

    public function exhibition()
    {
        return $this->belongsTo('App\Exhibition');
    }
    public function exhibition_zone()
    {
        return $this->belongsTo('App\ExhibitionZone');
    }
    public function users()
    {
        return $this->belongsToMany('App\User', 'user_exhibitor');
    }

    public function exhibitor_images()
    {
        return $this->hasMany('App\ExhibitorImage');
    }
    public function exhibitor_videos()
    {
        return $this->hasMany('App\ExhibitorVideo');
    }
}
