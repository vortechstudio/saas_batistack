<?php

namespace App\Models\Customer;

use App\Enum\Customer\CustomerRestrictedIpTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerRestrictedIp extends Model
{
    /** @use HasFactory<\Database\Factories\Customer\CustomerRestrictedIpFactory> */
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'authorize' => CustomerRestrictedIpTypeEnum::class,
        'alert' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
