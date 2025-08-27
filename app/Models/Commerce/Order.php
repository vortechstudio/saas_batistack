<?php

namespace App\Models\Commerce;

use App\Enum\Commerce\OrderStatusEnum;
use App\Enum\Commerce\OrderTypeEnum;
use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\Commerce\OrderFactory> */
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'status' => OrderStatusEnum::class,
        'type' => OrderTypeEnum::class,
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments()
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function logs()
    {
        return $this->hasMany(OrderLog::class);
    }

    // Scopes
    /**
     * Scope pour filtrer par statut
     */
    public function scopeByStatus($query, OrderStatusEnum $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope pour les commandes confirmées
     */
    public function scopeConfirmed($query)
    {
        return $query->whereNotNull('confirmed_at');
    }

    // Fonction d'action
    /**
     * Vérifie si la commande peut être annulée
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            OrderStatusEnum::PENDING,
            OrderStatusEnum::CONFIRMED,
        ]);
    }

    /**
     * Vérifie si la commande peut être remboursée
     */
    public function canBeRefunded(): bool
    {
        return in_array($this->status, [
            OrderStatusEnum::DELIVERED,
        ]);
    }

    /**
     * Marque la commande comme confirmée
     */
    public function markAsConfirmed(): void
    {
        $this->update([
            'status' => OrderStatusEnum::CONFIRMED,
            'confirmed_at' => now(),
        ]);
    }

    /**
     * Marque la commande comme livrée
     */
    public function markAsDelivered(): void
    {
        $this->update([
            'status' => OrderStatusEnum::DELIVERED,
            'delivered_at' => now(),
        ]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function($order) {
            if(empty($order->order_number)) {
                $order->order_number = $order->generateOrderNumber();
            }
        });
    }

    // Fonction résiduel
    /**
     * Génère un numéro de commande unique
     */
    private function generateOrderNumber(): string
    {
        do {
            // Format: ORD-YYYYMMDD-XXXXX (ex: ORD-20250126-A1B2C)
            $number = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        } while (self::where('order_number', $number)->exists());

        return $number;
    }

    /**
     * Calcule le total avec taxes et remises
     */
    public function calculateTotal(): float
    {
        return $this->subtotal + $this->tax_amount - $this->discount_amount;
    }

    public function getLatestPayment(): ?OrderPayment
    {
        return $this->payments()->latest()->first();
    }

    public function getTotalPaidAmount(): float
    {
        return (float) $this->payments()->completed()->sum('amount');
    }

    public function getTotalRefundedAmount(): float
    {
        return (float) $this->payments()->sum('refunded_amount');
    }

    public function isFullyPaid(): bool
    {
        return $this->getTotalPaidAmount() >= $this->total;
    }

    public function hasFailedPayments(): bool
    {
        return $this->payments()->failed()->exists();
    }
}
