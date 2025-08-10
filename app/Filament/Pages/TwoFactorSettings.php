<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class TwoFactorSettings extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-check';

    protected string $view = 'filament.pages.two-factor-settings';

    protected static ?string $title = 'Authentification à deux facteurs';
    
    protected static ?string $navigationLabel = '2FA';
    
    protected static ?int $navigationSort = 10;

    public static function canAccess(): bool
    {
        return Auth::check();
    }
}
