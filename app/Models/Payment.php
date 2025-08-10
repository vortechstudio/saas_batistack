<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'invoice_id',
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'payment_method_details',
        'failure_reason',
        'processed_at',
        'refunded_at',
        'refund_amount',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'status' => PaymentStatus::class,
        'payment_method' => PaymentMethod::class,
        'payment_method_details' => 'array',
        'processed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Relation avec le client
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relation avec la facture
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Scope pour les paiements réussis
     */
    public function scopeSucceeded($query)
    {
        return $query->where('status', PaymentStatus::SUCCEEDED);
    }

    /**
     * Scope pour les paiements échoués
     */
    public function scopeFailed($query)
    {
        return $query->where('status', PaymentStatus::FAILED);
    }

    /**
     * Scope pour les paiements en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', PaymentStatus::PENDING);
    }

    /**
     * Vérifie si le paiement a réussi
     */
    public function isSucceeded(): bool
    {
        return $this->status === PaymentStatus::SUCCEEDED;
    }

    /**
     * Vérifie si le paiement a échoué
     */
    public function isFailed(): bool
    {
        return $this->status === PaymentStatus::FAILED;
    }

    /**
     * Vérifie si le paiement est en attente
     */
    public function isPending(): bool
    {
        return $this->status === PaymentStatus::PENDING;
    }

    /**
     * Vérifie si le paiement est remboursé
     */
    public function isRefunded(): bool
    {
        return in_array($this->status, [PaymentStatus::REFUNDED, PaymentStatus::PARTIALLY_REFUNDED]);
    }

    /**
     * Marque le paiement comme réussi
     */
    public function markAsSucceeded(): void
    {
        $this->update([
            'status' => PaymentStatus::SUCCEEDED,
            'processed_at' => now(),
        ]);

        // Marquer la facture comme payée si elle existe
        if ($this->invoice) {
            $this->invoice->markAsPaid();
        }
    }

    /**
     * Marque le paiement comme échoué
     */
    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => PaymentStatus::FAILED,
            'failure_reason' => $reason,
        ]);
    }

    /**
     * Obtient le montant formaté
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . strtoupper($this->currency);
    }

    /**
     * Obtient le statut formaté
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    /**
     * Obtient la couleur du statut
     */
    public function getStatusColorAttribute(): string
    {
        return $this->status->color();
    }

    /**
     * Obtient le label de la méthode de paiement
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return $this->payment_method->label();
    }
}