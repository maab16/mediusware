<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantPrice extends Model
{
    public function variant1()
    {
        return $this->belongsTo('App\Models\ProductVariant', 'product_variant_one');
    }

    public function variant2()
    {
        return $this->belongsTo('App\Models\ProductVariant', 'product_variant_two');
    }

    public function variant3()
    {
        return $this->belongsTo('App\Models\ProductVariant', 'product_variant_three');
    }
}
