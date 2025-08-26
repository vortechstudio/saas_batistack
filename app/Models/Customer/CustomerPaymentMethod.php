<?php

namespace App\Models\Customer;

use App\Services\Stripe\CustomerService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPaymentMethod extends Model
{
    /** @use HasFactory<\Database\Factories\Customer\CustomerPaymentMethodFactory> */
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    protected function getStripePaymentMethodsAttribute()
    {
        $stripeApi = new CustomerService();
        return $stripeApi->listPaymentMethods($this->customer)->data;
    }
}
