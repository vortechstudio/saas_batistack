<?php

namespace App\Models;

use App\Enums\LicenseStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'product_id',
        'license_key',
        'domain',
        'status',
        'starts_at',
        'expires_at',
        'max_users',
        'current_users',
        'last_used_at',
    ];

    protected $casts = [
        'status' => LicenseStatus::class,
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'max_users' => 'integer',
        'current_users' => 'integer',
    ];

    /**
     * Génère automatiquement une clé de licence lors de la création
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($license) {
            if (empty($license->license_key)) {
                $license->license_key = self::generateLicenseKey();
            }

            if (empty($license->domain)) {
                $license->domain = self::generateDomain($license);
            }
        });
    }

    /**
     * Relation avec le client
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relation avec le produit
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relation many-to-many avec les modules
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'license_modules')
            ->withPivot(['enabled', 'expires_at'])
            ->withTimestamps();
    }

    /**
     * Relation many-to-many avec les options
     */
    public function options(): BelongsToMany
    {
        return $this->belongsToMany(Option::class, 'license_options')
            ->withPivot(['enabled', 'expires_at'])
            ->withTimestamps();
    }

    /**
     * Modules actifs pour cette licence
     */
    public function activeModules(): BelongsToMany
    {
        return $this->modules()
            ->wherePivot('enabled', true)
            ->where(function ($query) {
                $query->whereNull('license_modules.expires_at')
                    ->orWhere('license_modules.expires_at', '>', now());
            });
    }

    /**
     * Options actives pour cette licence
     */
    public function activeOptions(): BelongsToMany
    {
        return $this->options()
            ->wherePivot('enabled', true)
            ->where(function ($query) {
                $query->whereNull('license_options.expires_at')
                    ->orWhere('license_options.expires_at', '>', now());
            });
    }

    /**
     * Scope pour les licences actives
     */
    public function scopeActive($query)
    {
        return $query->where('status', LicenseStatus::ACTIVE);
    }

    /**
     * Scope pour les licences expirées
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope pour les licences valides (actives et non expirées)
     */
    public function scopeValid($query)
    {
        return $query->where('status', LicenseStatus::ACTIVE)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Vérifie si la licence est valide
     */
    public function isValid(): bool
    {
        return $this->status === LicenseStatus::ACTIVE &&
            ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /**
     * Vérifie si la licence est expirée
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Vérifie si un module est activé pour cette licence
     */
    public function hasModule(string $moduleKey): bool
    {
        return $this->activeModules()
            ->where('modules.key', $moduleKey)
            ->exists();
    }

    /**
     * Vérifie si une option est activée pour cette licence
     */
    public function hasOption(string $optionKey): bool
    {
        return $this->activeOptions()
            ->where('options.key', $optionKey)
            ->exists();
    }

    /**
     * Active un module pour cette licence
     */
    public function enableModule(int $moduleId, ?\DateTime $expiresAt = null): void
    {
        $this->modules()->syncWithoutDetaching([
            $moduleId => [
                'enabled' => true,
                'expires_at' => $expiresAt,
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Désactive un module pour cette licence
     */
    public function disableModule(int $moduleId): void
    {
        $this->modules()->updateExistingPivot($moduleId, [
            'enabled' => false,
            'updated_at' => now(),
        ]);
    }

    /**
     * Active une option pour cette licence
     */
    public function enableOption(int $optionId, ?\DateTime $expiresAt = null): void
    {
        $this->options()->syncWithoutDetaching([
            $optionId => [
                'enabled' => true,
                'expires_at' => $expiresAt,
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Désactive une option pour cette licence
     */
    public function disableOption(int $optionId): void
    {
        $this->options()->updateExistingPivot($optionId, [
            'enabled' => false,
            'updated_at' => now(),
        ]);
    }

    /**
     * Met à jour la dernière utilisation
     */
    public function updateLastUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Génère une clé de licence unique
     */
    public static function generateLicenseKey(): string
    {
        do {
            $key = 'BATI-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
        } while (self::where('license_key', $key)->exists());

        return $key;
    }

    /**
     * Trouve une licence par sa clé
     */
    public static function findByKey(string $licenseKey): ?self
    {
        return self::where('license_key', $licenseKey)->first();
    }

    /**
     * Génère un domaine unique basé sur le nom du client et le produit
     */
    public static function generateDomain($license): string
    {
        $customer = $license->customer ?? Customer::find($license->customer_id);
        $product = $license->product ?? Product::find($license->product_id);

        $baseDomain = Str::slug($customer->company_name . '-' . $product->name);
        $domain = $baseDomain;
        $counter = 1;

        while (self::where('domain', $domain)->exists()) {
            $domain = $baseDomain . '-' . $counter;
            $counter++;
        }

        return $domain;
    }

    /**
     * Retourne l'URL complète du service
     */
    public function getServiceUrl(): string
    {
        $baseDomain = config('app.service_domain', 'batistack.com');
        return "https://{$this->domain}.{$baseDomain}";
    }

    /**
     * Vérifie si le domaine est configuré
     */
    public function hasDomain(): bool
    {
        return !empty($this->domain);
    }
}
