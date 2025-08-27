<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerServiceStorage extends Model
{
    /** @use HasFactory<\Database\Factories\Customer\CustomerServiceStorageFactory> */
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'quota' => 'integer',
        'used' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
