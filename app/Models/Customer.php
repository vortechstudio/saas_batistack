<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable;

class Customer extends Model
{
    use HasFactory, Billable;

    protected $fillable = [
        'company_name',
        'contact_name',
        'email',
        'phone',
        'address',
        'city',
        'postal_code',
        'country',
        'siret',
        'vat_number',
        'status',
        'stripe_customer_id',
        'user_id',
    ];

    protected $casts = [
        'status' => CustomerStatus::class,
    ];

    /**
     * Relation avec l'utilisateur associé
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les licences du client
     */
    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }

    /**
     * Scope pour les clients actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', CustomerStatus::ACTIVE);
    }

    /**
     * Vérifie si le client est actif
     */
    public function isActive(): bool
    {
        return $this->status === CustomerStatus::ACTIVE;
    }

    /**
     * Obtient le nom complet pour l'affichage
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->company_name . ' (' . $this->contact_name . ')';
    }

    /**
     * Relation avec les factures
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Relation avec les paiements
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Obtient le nom pour Stripe
     */
    public function stripeName(): string
    {
        return $this->company_name;
    }

    /**
     * Obtient l'email pour Stripe
     */
    public function stripeEmail(): string
    {
        return $this->email;
    }

    /**
     * Obtient l'adresse pour Stripe
     */
    public function stripeAddress(): array
    {
        return [
            'line1' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
        ];
    }

    /**
     * Vérifie si le client a des factures en retard
     */
    public function hasOverdueInvoices(): bool
    {
        return $this->invoices()
            ->where('status', \App\Enums\InvoiceStatus::OVERDUE)
            ->exists();
    }

    /**
     * Obtient le montant total des factures impayées
     */
    public function getUnpaidInvoicesTotal(): float
    {
        return $this->invoices()
            ->whereIn('status', [
                \App\Enums\InvoiceStatus::PENDING,
                \App\Enums\InvoiceStatus::OVERDUE
            ])
            ->sum('total_amount');
    }
}
