<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'license_id',
        'description',
        'quantity',
        'unit_price',
        'total_price',
        'tax_rate',
        'tax_amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    /**
     * Relation avec la facture
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Relation avec le produit
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relation avec la licence
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    /**
     * Calcule le prix total
     */
    public function calculateTotal(): void
    {
        $subtotal = $this->quantity * $this->unit_price;
        $this->tax_amount = $subtotal * ($this->tax_rate / 100);
        $this->total_price = $subtotal + $this->tax_amount;
    }

    /**
     * Obtient le prix unitaire formaté
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return number_format($this->unit_price, 2) . ' €';
    }

    /**
     * Obtient le prix total formaté
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return number_format($this->total_price, 2) . ' €';
    }
}