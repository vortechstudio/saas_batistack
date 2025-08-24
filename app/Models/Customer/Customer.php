<?php

namespace App\Models\Customer;

use App\Enum\Customer\CustomerTypeEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\Customer\CustomerFactory> */
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'type_compte' => CustomerTypeEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            $customer->code_client = 'CLI' . str_pad($customer->id, 4, '0', STR_PAD_LEFT);
        });
    }
}
