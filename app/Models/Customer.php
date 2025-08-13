<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable;
use Stripe\StripeClient;

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

    /**
     * Créer une subscription pour une licence
     */
    public function createLicenseSubscription($product, $billingCycle, $modules = [], $options = [])
    {
        $priceId = $this->getStripePriceId($product, $billingCycle);

        // Créer la subscription avec le prix principal
        $subscription = $this->newSubscription('license', $priceId)->create();

        // Ajouter les modules comme items supplémentaires à la subscription existante
        foreach ($modules as $module) {
            $modulePriceId = $this->getModuleStripePriceId($module, $billingCycle);
            $subscription->addPrice($modulePriceId);
        }

        // Ajouter les options à la subscription existante
        foreach ($options as $option) {
            $optionPriceId = $this->getOptionStripePriceId($option);
            $subscription->addPrice($optionPriceId);
        }

        return $subscription;
    }

    /**
     * Obtenir l'ID du prix Stripe pour un produit
     */
    private function getStripePriceId($product, $billingCycle)
    {
        return $billingCycle === 'yearly'
            ? $product->stripe_price_id_yearly
            : $product->stripe_price_id_monthly;
    }

    /**
     * Obtenir l'ID du prix Stripe pour un module
     */
    private function getModuleStripePriceId($module, $billingCycle)
    {
        return $billingCycle === 'yearly'
            ? $module->stripe_price_id_yearly
            : $module->stripe_price_id_monthly;
    }

    /**
     * Obtenir l'ID du prix Stripe pour une option
     */
    private function getOptionStripePriceId($option)
    {
        // Les options peuvent avoir un prix unique ou selon le cycle
        return $option->stripe_price_id ?? $option->stripe_price_id_monthly;
    }

    /**
     * Get a Stripe client instance
     *
     * @return \Stripe\StripeClient
     */
    public function stripe(): StripeClient
    {
        return new StripeClient(config('services.stripe.secret'));
    }
}
