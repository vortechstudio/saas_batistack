<?php

namespace App\Observers\Customer;

use App\Models\Customer\Customer;

class CustomerObserver
{
    public function creating(Customer $customer): void
    {
        $customer->code_client = 'CLI'.rand(100000,999999999);
    }
}
