<?php

namespace App\Models;

use App\Enums\ModuleCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'category',
        'base_price',
        'is_active',
        'sort_order',
        'version',
        'composer_path',
    ];

    protected $casts = [
        'category' => ModuleCategory::class,
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Relation many-to-many avec les produits
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_modules')
            ->withPivot(['included', 'price_override'])
            ->withTimestamps();
    }

    /**
     * Relation many-to-many avec les licences
     */
    public function licenses(): BelongsToMany
    {
        return $this->belongsToMany(License::class, 'license_modules')
            ->withPivot(['enabled', 'expires_at'])
            ->withTimestamps();
    }

    /**
     * Scope pour les modules actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope par catégorie
     */
    public function scopeByCategory($query, ModuleCategory $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope ordonné par sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Vérifie si le module est actif
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }
}
