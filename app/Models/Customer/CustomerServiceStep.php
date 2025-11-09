<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerServiceStep extends Model
{
    /** @use HasFactory<\Database\Factories\Customer\CustomerServiceStepFactory> */
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'done' => 'boolean',
    ];

    public function customerService()
    {
        return $this->belongsTo(CustomerService::class);
    }
}
