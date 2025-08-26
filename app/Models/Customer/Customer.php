<?php

namespace App\Models\Customer;

use App\Enum\Customer\CustomerSupportTypeEnum;
use App\Enum\Customer\CustomerTypeEnum;
use App\Models\Commerce\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\Stripe\CustomerService as StripeCustomerService;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\Customer\CustomerFactory> */
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'type_compte' => CustomerTypeEnum::class,
        'support_type' => CustomerSupportTypeEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restrictedIps()
    {
        return $this->hasMany(CustomerRestrictedIp::class);
    }

    public function services()
    {
        return $this->hasMany(CustomerService::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /** Attributes */
    protected function getSupportTypeColorAttributes()
    {
        return $this->support_type;
    }

    protected static function booted(): void
    {
        static::creating(function (Customer $customer, StripeCustomerService $customerService) {
            $customer->code_client = 'CLI' . str_pad($customer->id, 4, '0', STR_PAD_LEFT);
            $customerService->create($customer);
        });
    }

    public function listPaymentMethods()
    {
        return app(StripeCustomerService::class)->listPaymentMethods($this);
    }

    public function hasPaymentMethods(): bool
    {
        return $this->listPaymentMethods()->count() > 0;
    }

    public function getListInvoices()
    {
        return app(StripeCustomerService::class)->listInvoices($this);
    }
}
