<?php

namespace App\Models\Commerce;

use App\Models\Product\Product;
use App\Models\Product\ProductPrice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    /** @use HasFactory<\Database\Factories\Commerce\OrderItemFactory> */
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productPrice()
    {
        return $this->belongsTo(ProductPrice::class);
    }

    protected static function booted(): void
    {
        static::saving(function (OrderItem $orderItem) {
            // Calcul automatique du prix total
            $orderItem->total_price = $orderItem->unit_price * $orderItem->quantity;
        });
    }

    // Méthodes utilitaires
    public function getFormattedUnitPrice(): string
    {
        return number_format($this->unit_price, 2) . ' EUR';
    }

    public function getFormattedTotalPrice(): string
    {
        return number_format($this->total_price, 2) . ' EUR';
    }

    public function getTotalAmount(): float
    {
        return (float) $this->total_price;
    }
}
