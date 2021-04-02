<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExhibitorImage extends Model
{
    protected $fillable = [
        'exhibitor_id',
        'image_path',
        'sort_index'
    ];

    public function exhibitor()
    {
        return $this->belongsTo('App\Exhibitor');
    }
}
