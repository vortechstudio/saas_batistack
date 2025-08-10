<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use PragmaRX\Google2FA\Google2FA;
use Livewire\Attributes\Validate;

class TwoFactorVerify extends Component
{
    #[Validate('required|string')]
    public $code = '';

    #[Validate('required|string')]
    public $recovery_code = '';

    public $use_recovery_code = false;
    public $attempts = 0;
    public $max_attempts = 5;

    public function mount()
    {
        // Vérifier si l'utilisateur est connecté et a la 2FA activée
        if (!Auth::check() || !Auth::user()->two_factor_enabled) {
            return redirect()->route('login');
        }
    }

    public function verify()
    {
        if ($this->attempts >= $this->max_attempts) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            return redirect()->route('login')->with('error', 'Trop de tentatives échouées. Veuillez vous reconnecter.');
        }

        if ($this->use_recovery_code) {
            return $this->verifyRecoveryCode();
        }

        $this->validate([
            'code' => 'required|string|size:6'
        ]);

        $user = Auth::user();
        $google2fa = new Google2FA();
        $secret = decrypt($user->two_factor_secret);

        $valid = $google2fa->verifyKey($secret, $this->code);

        if (!$valid) {
            $this->attempts++;
            $this->addError('code', 'Le code de vérification est incorrect.');
            return;
        }

        // Marquer la 2FA comme vérifiée pour cette session
        session(['2fa_verified' => true]);

        // Mettre à jour la dernière vérification
        $user->update([
            'two_factor_verified_at' => now()
        ]);

        // Rediriger vers la destination prévue ou le dashboard
        return redirect()->intended(route('filament.admin.pages.dashboard'));
    }

    public function verifyRecoveryCode()
    {
        $this->validate([
            'recovery_code' => 'required|string'
        ]);

        $user = Auth::user();
        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

        $recoveryCode = strtoupper(str_replace(' ', '', $this->recovery_code));

        if (!in_array($recoveryCode, $recoveryCodes)) {
            $this->attempts++;
            $this->addError('recovery_code', 'Le code de récupération est incorrect.');
            return;
        }

        // Supprimer le code de récupération utilisé
        $recoveryCodes = array_diff($recoveryCodes, [$recoveryCode]);

        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode(array_values($recoveryCodes))),
            'two_factor_verified_at' => now()
        ]);

        // Marquer la 2FA comme vérifiée pour cette session
        session(['2fa_verified' => true]);

        session()->flash('message', 'Code de récupération utilisé avec succès. Pensez à régénérer vos codes de récupération.');

        return redirect()->intended(route('filament.admin.pages.dashboard'));
    }

    public function toggleRecoveryCode()
    {
        $this->use_recovery_code = !$this->use_recovery_code;
        $this->reset(['code', 'recovery_code']);
        $this->resetErrorBag();
    }

    #[Layout('components.layouts.auth')]
    public function render()
    {
        return view('livewire.auth.two-factor-verify');
    }
}
