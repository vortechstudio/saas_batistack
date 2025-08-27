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

    public function paymentMethods()
    {
        return $this->hasMany(CustomerPaymentMethod::class);
    }

    /** Attributes */
    protected function getSupportTypeColorAttributes()
    {
        return $this->support_type;
    }

    protected static function booted(): void
    {
        static::created(function (Customer $customer) {
            $customer->code_client = 'CLI' . str_pad($customer->id, 4, '0', STR_PAD_LEFT);
            $customer->save();

            //$customerService = app(\App\Services\Stripe\StripeCustomerService::class);
            //$customerService->create($customer);
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
        return app(StripeCustomerService::class)->listInvoices($this)
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'metadata' => $invoice->metadata ? $invoice->metadata->toArray() : [],
                    'created' => $invoice->created,
                    'subtotal' => $this->calcHorsTaxe($invoice->subtotal/100, 20),
                    'total' => $invoice->total/100,
                    'amount_due' => $invoice->amount_due/100,
                    'status' => $invoice->status,
                ];
            })
            ->toArray();
    }

    public function getInvoice($id)
    {
        return app(StripeCustomerService::class)->getInvoice($id);
    }

    private function calcHorsTaxe(float $total, float $taxe): float
    {
        return $total - ($total * $taxe / 100);
    }
}
