<?php

namespace App\Models\Customer;

use App\Enum\Customer\CustomerServiceStatusEnum;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomerService extends Model
{
    /** @use HasFactory<\Database\Factories\Customer\CustomerServiceFactory> */
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'status' => CustomerServiceStatusEnum::class,
        'creationDate' => 'date',
        'expirationDate' => 'date',
        'nextBillingDate' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function steps()
    {
        return $this->hasMany(CustomerServiceStep::class);
    }

    public function options()
    {
        return $this->hasMany(CustomerServiceOption::class);
    }

    public function modules()
    {
        return $this->hasMany(CustomerServiceModule::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customerService) {
            if (empty($customerService->service_code)) {
                $customerService->service_code = $customerService->generateServiceCode();
            }
        });
    }

    /**
     * Génère un code de service unique
     */
    private function generateServiceCode(): string
    {
        do {
            // Format: SRV-YYYYMMDD-XXXXX (ex: SRV-20250126-A1B2C)
            $code = 'SRV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        } while (self::where('service_code', $code)->exists());

        return $code;
    }

    /**
     * Alternative: Code basé sur l'ID du client et du produit
     */
    private function generateServiceCodeAlternative(): string
    {
        do {
            // Format: SRV-{customer_id}-{product_id}-{random}
            $code = sprintf(
                'SRV-%d-%d-%s',
                $this->customer_id,
                $this->product_id,
                strtoupper(Str::random(4))
            );
        } while (self::where('service_code', $code)->exists());

        return $code;
    }


}
