<?php

namespace App\Models\Product;

use App\Enum\Product\ProductCategoryEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\Product\ProductFactory> */
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'category' => ProductCategoryEnum::class,
        'active' => 'boolean',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            $product->slug = Str::slug($$product->name);
        });
    }
}
