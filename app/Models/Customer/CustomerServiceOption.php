<?php

namespace App\Models\Customer;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Model;

class CustomerServiceOption extends Model
{
    protected $guarded = [];

    protected $casts = [
        'settings' => 'array',
    ];

    public function customerService()
    {
        return $this->belongsTo(CustomerService::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
