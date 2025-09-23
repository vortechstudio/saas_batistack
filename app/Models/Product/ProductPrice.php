<?php
namespace App\Models\Product;

use App\Services\Stripe\StripeService;
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
    protected $appends = ['info_stripe'];


    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getInfoPriceStripe()
    {
        return app(StripeService::class)->client->prices->retrieve($this->stripe_price_id);
    }

    public function getInfoStripeAttribute()
    {
        return $this->getInfoPriceStripe();
    }
}
