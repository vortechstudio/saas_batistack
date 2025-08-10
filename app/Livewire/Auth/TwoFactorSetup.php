<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;

class TwoFactorSetup extends Component
{
    #[Validate('required|string')]
    public $password = '';
    
    #[Validate('required|string|size:6')]
    public $verification_code = '';
    
    public $qr_code = '';
    public $secret_key = '';
    public $recovery_codes = [];
    public $step = 'password'; // password, setup, verify, complete
    public $show_recovery_codes = false;

    public function mount()
    {
        if (Auth::user()->two_factor_enabled) {
            $this->step = 'complete';
        }
    }

    public function verifyPassword()
    {
        $this->validate([
            'password' => 'required|string'
        ]);

        if (!Hash::check($this->password, Auth::user()->password)) {
            $this->addError('password', 'Le mot de passe est incorrect.');
            return;
        }

        $this->generateSecretKey();
        $this->step = 'setup';
    }

    public function generateSecretKey()
    {
        $google2fa = new Google2FA();
        $this->secret_key = $google2fa->generateSecretKey();
        
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            Auth::user()->email,
            $this->secret_key
        );
        
        $this->qr_code = $qrCodeUrl;
    }

    public function verifySetup()
    {
        $this->validate([
            'verification_code' => 'required|string|size:6'
        ]);

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($this->secret_key, $this->verification_code);

        if (!$valid) {
            $this->addError('verification_code', 'Le code de vérification est incorrect.');
            return;
        }

        // Générer les codes de récupération
        $this->recovery_codes = $this->generateRecoveryCodes();

        // Sauvegarder la configuration 2FA
        $user = Auth::user();
        $user->update([
            'two_factor_secret' => encrypt($this->secret_key),
            'two_factor_recovery_codes' => encrypt(json_encode($this->recovery_codes)),
            'two_factor_enabled' => true,
            'two_factor_verified_at' => now()
        ]);

        $this->step = 'complete';
        session()->flash('message', 'L\'authentification à deux facteurs a été activée avec succès !');
    }

    public function generateRecoveryCodes()
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
        }
        return $codes;
    }

    public function showRecoveryCodes()
    {
        $this->show_recovery_codes = true;
    }

    public function disable2FA()
    {
        $user = Auth::user();
        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_enabled' => false,
            'two_factor_verified_at' => null
        ]);

        $this->step = 'password';
        $this->reset(['password', 'verification_code', 'qr_code', 'secret_key', 'recovery_codes']);
        session()->flash('message', 'L\'authentification à deux facteurs a été désactivée.');
    }

    public function render()
    {
        return view('livewire.auth.two-factor-setup');
    }
}
