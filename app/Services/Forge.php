<?php

namespace App\Services;

class Forge
{
    public \Laravel\Forge\Forge $client;

    /**
     * Initialise le service Forge et crée un client \Laravel\Forge\Forge à partir de la clé API définie dans la configuration.
     */
    public function __construct()
    {
        $this->client = new \Laravel\Forge\Forge(config('batistack.forge_api_key'));
    }
    /**
     * Récupère l'adresse IP du premier serveur enregistré dans Forge.
     *
     * @return string|null L'adresse IP du premier serveur, ou `null` si aucun serveur n'est trouvé.
     */
    public function getIpAddressServer()
    {
        $forge = new \Laravel\Forge\Forge(config('batistack.forge_api_key'));
        return collect($forge->servers())->first()->ipAddress;
    }
}