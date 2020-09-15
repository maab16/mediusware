<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];

    public function image()
    {
        return $this->hasOne('App\Models\ProductImage');
    }

    public function variant()
    {
        return $this->hasMany('App\Models\ProductVariant');
    }

    public function prices()
    {
        return $this->hasMany('App\Models\ProductVariantPrice');
    }
}
