<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\Customer;
use App\Enums\CustomerStatus;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Contracts\Auth\MustVerifyEmail;

#[Layout('components.layouts.auth')]
class Register extends Component
{
    // Informations utilisateur
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    // Informations client/entreprise
    public string $company_name = '';
    public string $contact_name = '';
    public string $phone = '';
    public string $address = '';
    public string $city = '';
    public string $postal_code = '';
    public string $country = 'FR';
    public string $siret = '';
    public string $vat_number = '';

    // Étape du formulaire
    public int $currentStep = 1;
    public int $totalSteps = 2;

    // État de vérification email
    public bool $emailVerificationSent = false;

    /**
     * Règles de validation pour l'étape 1 (Utilisateur)
     */
    protected function getStep1Rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ];
    }

    /**
     * Règles de validation pour l'étape 2 (Client/Entreprise)
     */
    protected function getStep2Rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'country' => ['required', 'string', 'max:2'],
            'siret' => ['nullable', 'string', 'max:14'],
            'vat_number' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Passer à l'étape suivante
     */
    public function nextStep(): void
    {
        if ($this->currentStep === 1) {
            $this->validate($this->getStep1Rules());
            $this->currentStep = 2;
            // Pré-remplir le nom de contact avec le nom de l'utilisateur
            if (empty($this->contact_name)) {
                $this->contact_name = $this->name;
            }
        }
    }

    /**
     * Revenir à l'étape précédente
     */
    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    /**
     * Gérer l'inscription complète
     */
    public function register(): void
    {
        // Valider toutes les données
        $userValidated = $this->validate($this->getStep1Rules());
        $customerValidated = $this->validate($this->getStep2Rules());

        DB::transaction(function () use ($userValidated, $customerValidated) {
            // Créer l'utilisateur
            $userValidated['password'] = Hash::make($userValidated['password']);
            $user = User::create($userValidated);

            // Créer le profil client
            $customerData = array_merge($customerValidated, [
                'user_id' => $user->id,
                'email' => $user->email, // Utiliser l'email de l'utilisateur
                'status' => CustomerStatus::ACTIVE,
            ]);

            Customer::create($customerData);

            // Déclencher l'événement d'inscription (envoi automatique de l'email de vérification)
            event(new Registered($user));

            // Connecter l'utilisateur (optionnel - peut être désactivé si vous voulez forcer la vérification)
            Auth::login($user);

            // Marquer que l'email de vérification a été envoyé
            $this->emailVerificationSent = true;
        });

        // Rediriger vers la page de vérification email ou tableau de bord
        if ($this->emailVerificationSent) {
            $this->redirect(route('verification.notice', absolute: false), navigate: true);
        } else {
            $this->redirect(route('dashboard', absolute: false), navigate: true);
        }
    }

    /**
     * Renvoyer l'email de vérification
     */
    public function resendVerificationEmail(): void
    {
        $user = Auth::user();

        if ($user && $user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();

            session()->flash('status', 'verification-link-sent');
        }
    }

    /**
     * Obtenir le pourcentage de progression
     */
    public function getProgressPercentage(): int
    {
        return (int) (($this->currentStep / $this->totalSteps) * 100);
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
