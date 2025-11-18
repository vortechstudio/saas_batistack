<?php

namespace App\Services\Ovh;

use Ovh\Api;

class OvhWrapper
{
    /**
     * Exécute un appel vers l'API OVH en utilisant la méthode HTTP spécifiée.
     *
     * Tente d'appeler la méthode dynamique `$method` sur le client OVH avec le chemin `$path`
     * et les données `$data`. En cas d'erreur, le message d'erreur est enregistré dans le log
     * et `null` est retourné.
     *
     * @param string $method Nom de la méthode HTTP à invoquer (par ex. 'get', 'post', 'put', 'delete').
     * @param string $path Chemin de la ressource OVH à appeler.
     * @param array $data Données à envoyer avec la requête.
     * @return mixed|null Le résultat retourné par l'appel à l'API OVH, ou `null` si une erreur est survenue.
     */
    public function call(string $method, string $path, array $data = [])
    {
        try {
            $request = new Api(
                config('services.ovh.app_key'),
                config('services.ovh.app_secret'),
                config('services.ovh.endpoint'),
                config('services.ovh.app_consume_key'),
            );

            return $request->$method($path, $data);
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            return null;
        }
    }
}