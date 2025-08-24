<?php

namespace App\Models\Product;

use App\Enum\Product\ProductPriceFrequencyEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPrice extends Model
{
    /** @use HasFactory<\Database\Factories\Product\ProductPriceFactory> */
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'frequency' => ProductPriceFrequencyEnum::class,
        'price' => 'float',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
