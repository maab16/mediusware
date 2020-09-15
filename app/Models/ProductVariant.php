<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    public function getVariantAttribute($value)
    {
        return explode(',', $value);
    }
}
