<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;

class CustomerServiceBackup extends Model
{
    protected $guarded = [];

    public function customerService()
    {
        return $this->belongsTo(CustomerService::class);
    }
}
