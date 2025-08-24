<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    /** @use HasFactory<\Database\Factories\Product\FeatureFactory> */
    use HasFactory;
    protected $guarded = [];
    
    public function products()
    {
        return $this->belongsToMany(Product::class, 'feature_product');
    }
}
