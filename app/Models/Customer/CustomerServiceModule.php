<?php

namespace App\Models\Customer;

use App\Models\Product\Feature;
use Illuminate\Database\Eloquent\Model;

class CustomerServiceModule extends Model
{
    protected $guarded = [];

    public function customerService()
    {
        return $this->belongsTo(CustomerService::class);
    }

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }
}
