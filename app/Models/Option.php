<?php

namespace App\Models;

use App\Enums\BillingCycle;
use App\Enums\OptionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Option extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'type',
        'price',
        'billing_cycle',
        'is_active',
    ];

    protected $casts = [
        'type' => OptionType::class,
        'price' => 'decimal:2',
        'billing_cycle' => BillingCycle::class,
        'is_active' => 'boolean',
    ];

    /**
     * Relation many-to-many avec les produits
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_options')
            ->withTimestamps();
    }

    /**
     * Relation many-to-many avec les licences
     */
    public function licenses(): BelongsToMany
    {
        return $this->belongsToMany(License::class, 'license_options')
            ->withPivot(['enabled', 'expires_at'])
            ->withTimestamps();
    }

    /**
     * Scope pour les options actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope par type
     */
    public function scopeByType($query, OptionType $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope par cycle de facturation
     */
    public function scopeByBillingCycle($query, BillingCycle $cycle)
    {
        return $query->where('billing_cycle', $cycle);
    }

    /**
     * Vérifie si l'option est active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }
}
