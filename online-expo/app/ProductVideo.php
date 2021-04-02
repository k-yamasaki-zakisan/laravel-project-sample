<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductVideo extends Model
{
    protected $fillable = [
        'product_id',
        'embed_code',
        'sort_index'
    ];

    public function product()
    {
        return $this->belongsTo('App\Product');
    }
}
