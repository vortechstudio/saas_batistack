<?php

namespace App\Models\Product;

use App\Enum\Product\ProductCategoryEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\Product\ProductFactory> */
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'category' => ProductCategoryEnum::class,
        'active' => 'boolean',
    ];
}
