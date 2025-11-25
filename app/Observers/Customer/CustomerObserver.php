<?php

namespace App\Observers\Customer;

use App\Models\Customer\Customer;

class CustomerObserver
{
    /**
     * Génère et assigne un identifiant client au modèle avant sa création.
     *
     * Génère un code en préfixant "CLI" à un entier aléatoire compris entre 100000 et 999999999, puis l'assigne à la propriété `$customer->code_client`.
     *
     * @param Customer $customer L'instance Customer en cours de création.
     */
    public function creating(Customer $customer): void
    {
        do {
            $code = 'CLI' . str_pad(rand(100000, 9999999), 7, '0', STR_PAD_LEFT);
        } while (Customer::where('code_client', $code)->exists());

        $customer->code_client = $code;
    }
}
