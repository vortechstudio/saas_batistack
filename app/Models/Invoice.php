<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'invoice_number',
        'stripe_invoice_id',
        'status',
        'subtotal_amount',
        'tax_amount',
        'total_amount',
        'currency',
        'due_date',
        'paid_at',
        'description',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'status' => InvoiceStatus::class,
        'subtotal_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
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
     * Relation avec les lignes de facture
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Relation avec les paiements
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Relation avec le paiement principal
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->where('status', \App\Enums\PaymentStatus::SUCCEEDED);
    }

    /**
     * Scope pour les factures en retard
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->where('status', InvoiceStatus::PENDING);
    }

    /**
     * Scope pour les factures payées
     */
    public function scopePaid($query)
    {
        return $query->where('status', InvoiceStatus::PAID);
    }

    /**
     * Scope pour les factures en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', InvoiceStatus::PENDING);
    }

    /**
     * Vérifie si la facture est en retard
     */
    public function isOverdue(): bool
    {
        return $this->due_date < now() && $this->status === InvoiceStatus::PENDING;
    }

    /**
     * Vérifie si la facture est payée
     */
    public function isPaid(): bool
    {
        return $this->status === InvoiceStatus::PAID;
    }

    /**
     * Marque la facture comme payée
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => InvoiceStatus::PAID,
            'paid_at' => now(),
        ]);
    }

    /**
     * Marque la facture comme en retard
     */
    public function markAsOverdue(): void
    {
        $this->update([
            'status' => InvoiceStatus::OVERDUE,
        ]);
    }

    /**
     * Génère un numéro de facture unique
     */
    public static function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $month = now()->format('m');
        $lastInvoice = static::whereYear('created_at', $year)
            ->whereMonth('created_at', now()->month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) + 1 : 1;

        return sprintf('INV-%s%s-%04d', $year, $month, $sequence);
    }

    /**
     * Obtient le montant formaté
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total_amount, 2) . ' ' . strtoupper($this->currency);
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
}
