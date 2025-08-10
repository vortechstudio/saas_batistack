<?php

namespace App\Models;

use App\Enums\BillingCycle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'base_price',
        'billing_cycle',
        'max_users',
        'max_projects',
        'storage_limit',
        'is_active',
        'is_featured',
        'stripe_price_id',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'billing_cycle' => BillingCycle::class,
        'max_users' => 'integer',
        'max_projects' => 'integer',
        'storage_limit' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Relation avec les licences
     */
    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }

    /**
     * Relation many-to-many avec les modules
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'product_modules')
            ->withPivot(['included', 'price_override'])
            ->withTimestamps();
    }

    /**
     * Relation many-to-many avec les options
     */
    public function options(): BelongsToMany
    {
        return $this->belongsToMany(Option::class, 'product_options')
            ->withTimestamps();
    }

    /**
     * Modules inclus dans le produit
     */
    public function includedModules(): BelongsToMany
    {
        return $this->modules()->wherePivot('included', true);
    }

    /**
     * Modules optionnels pour le produit
     */
    public function optionalModules(): BelongsToMany
    {
        return $this->modules()->wherePivot('included', false);
    }

    /**
     * Scope pour les produits actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les produits en vedette
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope par cycle de facturation
     */
    public function scopeByBillingCycle($query, BillingCycle $cycle)
    {
        return $query->where('billing_cycle', $cycle);
    }

    /**
     * Calcule le prix total avec les modules optionnels
     */
    public function calculateTotalPrice(array $moduleIds = [], array $optionIds = []): float
    {
        $total = $this->base_price;

        // Ajouter le prix des modules optionnels
        if (!empty($moduleIds)) {
            $optionalModules = $this->modules()
                ->whereIn('modules.id', $moduleIds)
                ->wherePivot('included', false)
                ->get();

            foreach ($optionalModules as $module) {
                $price = $module->pivot->price_override ?? $module->base_price;
                $total += $price;
            }
        }

        // Ajouter le prix des options
        if (!empty($optionIds)) {
            $options = $this->options()
                ->whereIn('options.id', $optionIds)
                ->get();

            foreach ($options as $option) {
                $total += $option->price;
            }
        }

        return $total;
    }

    /**
     * Vérifie si le produit est actif
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Vérifie si le produit est en vedette
     */
    public function isFeatured(): bool
    {
        return $this->is_featured;
    }
}
