<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relation avec le client associé
     */
    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    /**
     * Relation avec les notifications
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    /**
     * Vérifie si l'utilisateur a un client associé
     */
    public function hasCustomer(): bool
    {
        return $this->customer()->exists();
    }

    /**
     * Vérifie si l'utilisateur est un administrateur
     */
    public function isAdmin(): bool
    {
        return str_ends_with($this->email, '@batistack.com');
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadNotificationsCountAttribute(): int
    {
        return $this->notifications()->whereNull('read_at')->count();
    }

    /**
     * Get high priority unread notifications count
     */
    public function getHighPriorityNotificationsCountAttribute(): int
    {
        return $this->notifications()
            ->whereNull('read_at')
            ->where('priority', '<=', 3)
            ->count();
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return str_ends_with($this->email, '@batistack.com') && $this->hasVerifiedEmail();
    }
}
