<?php
namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    protected $table = 'product_prices';
    protected $fillable = [
        'product_id',
        'price',
        'currency',
        'starts_at',
        'ends_at',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
