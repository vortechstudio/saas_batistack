<?php

namespace App\Models\Commerce;

use App\Enum\Commerce\OrderPaymentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrderPayment extends Model
{
    /** @use HasFactory<\Database\Factories\Commerce\OrderPaymentFactory> */
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'status' => OrderPaymentStatusEnum::class,
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'failed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'gateway_response' => 'array',
        'metadata' => 'array',
    ];

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Events
    protected static function booted(): void
    {
        static::creating(function (OrderPayment $payment) {
            if (empty($payment->reference)) {
                $payment->reference = $payment->generateReference();
            }
        });
    }

    // Méthodes de génération
    private function generateReference(): string
    {
        do {
            // Format: PAY-YYYYMMDD-XXXXX (ex: PAY-20250126-A1B2C)
            $reference = 'PAY-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        } while (self::where('reference', $reference)->exists());
        
        return $reference;
    }

    // Méthodes de statut
    public function isPending(): bool
    {
        return $this->status === OrderPaymentStatusEnum::PENDING;
    }

    public function isProcessing(): bool
    {
        return $this->status === OrderPaymentStatusEnum::PROCESSING;
    }

    public function isCompleted(): bool
    {
        return $this->status === OrderPaymentStatusEnum::COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status->isFailed();
    }

    public function isRefunded(): bool
    {
        return $this->status->isRefunded();
    }

    public function canBeRefunded(): bool
    {
        return $this->isCompleted() && $this->refunded_amount < $this->amount;
    }

    // Méthodes de gestion des paiements
    public function markAsCompleted(array $gatewayResponse = []): void
    {
        $this->update([
            'status' => OrderPaymentStatusEnum::COMPLETED,
            'processed_at' => now(),
            'gateway_response' => $gatewayResponse,
        ]);
    }

    public function markAsFailed(?string $reason = null, array $gatewayResponse = []): void
    {
        $this->update([
            'status' => OrderPaymentStatusEnum::FAILED,
            'failed_at' => now(),
            'failure_reason' => $reason,
            'gateway_response' => $gatewayResponse,
        ]);
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status' => OrderPaymentStatusEnum::PROCESSING,
        ]);
    }

    // Méthodes de remboursement
    public function refund(?float $amount = null, ?string $reason = null): void
    {
        $refundAmount = $amount ?? $this->amount;
        $totalRefunded = $this->refunded_amount + $refundAmount;
        
        $status = $totalRefunded >= $this->amount 
            ? OrderPaymentStatusEnum::REFUNDED 
            : OrderPaymentStatusEnum::PARTIALLY_REFUNDED;

        $this->update([
            'status' => $status,
            'refunded_amount' => $totalRefunded,
            'refund_reason' => $reason,
            'refunded_at' => now(),
        ]);
    }

    // Méthodes Stripe
    public function syncWithStripe(array $stripeData): void
    {
        $this->update([
            'stripe_payment_intent_id' => $stripeData['payment_intent_id'] ?? $this->stripe_payment_intent_id,
            'stripe_charge_id' => $stripeData['charge_id'] ?? $this->stripe_charge_id,
            'stripe_payment_method_id' => $stripeData['payment_method_id'] ?? $this->stripe_payment_method_id,
            'stripe_customer_id' => $stripeData['customer_id'] ?? $this->stripe_customer_id,
            'gateway_response' => array_merge($this->gateway_response ?? [], $stripeData),
        ]);
    }

    // Méthodes utilitaires
    public function getFormattedAmount(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    public function getFormattedRefundedAmount(): string
    {
        return number_format($this->refunded_amount, 2) . ' ' . $this->currency;
    }

    public function getRemainingAmount(): float
    {
        return (float) ($this->amount - $this->refunded_amount);
    }

    public function getFormattedRemainingAmount(): string
    {
        return number_format($this->getRemainingAmount(), 2) . ' ' . $this->currency;
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', OrderPaymentStatusEnum::COMPLETED);
    }

    public function scopeFailed($query)
    {
        return $query->whereIn('status', [OrderPaymentStatusEnum::FAILED, OrderPaymentStatusEnum::CANCELLED]);
    }

    public function scopePending($query)
    {
        return $query->where('status', OrderPaymentStatusEnum::PENDING);
    }

    public function scopeRefunded($query)
    {
        return $query->whereIn('status', [OrderPaymentStatusEnum::REFUNDED, OrderPaymentStatusEnum::PARTIALLY_REFUNDED]);
    }

    public function scopeByPaymentMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }
}
