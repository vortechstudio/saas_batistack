<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

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
}
