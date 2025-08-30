<?php

namespace App\Models\Product;

use App\Enum\Product\ProductCategoryEnum;
use App\Services\Stripe\StripeService;
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
        return $this->hasMany(\App\Models\Product\ProductPrice::class);
    }

    public function features()
    {
        return $this->belongsToMany(Feature::class, 'feature_product');
    }

    public function getInfoProductStripe()
    {
        return app(StripeService::class)->client->products->retrieve($this->stripe_product_id);
    }

    public function getInfoStripeAttribute()
    {
        return $this->getInfoProductStripe();
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            $product->slug = Str::slug($product->name);
        });
    }
}
