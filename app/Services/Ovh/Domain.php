<?php

namespace App\Services\Ovh;

use App\Services\Ovh\OvhWrapper;

class Domain extends OvhWrapper
{
    /**
     * Récupère la liste des domaines depuis l'API OVH.
     *
     * @return array Tableau des domaines renvoyé par l'API.
     */
    public function list(): array
    {
        return $this->call('get', '/domain');
    }

    /**
     * Vérifie si un enregistrement DNS A existe pour le sous-domaine fourni dans la zone batistack.ovh.
     *
     * @param string $subdomain Le sous-domaine à vérifier (par exemple "www" ou une entrée vide pour la racine).
     * @return bool `true` si au moins un enregistrement A existe pour ce sous-domaine, `false` sinon.
     */
    public function verify(string $subdomain): bool
    {
        $request = $this->call('get', '/domain/zone/batistack.ovh/record', [
            'fieldType' => 'A',
            'subDomain' => $subdomain,
        ]);

        if(count($request) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Crée un enregistrement DNS de type A pour le sous-domaine spécifié.
     *
     * Envoie une requête à l'API OVH pour ajouter un enregistrement A avec un TTL de 0.
     *
     * @param string $domain Le nom du sous-domaine à créer (valeur mise dans `subDomain`).
     * @param string $ip L'adresse IPv4 cible du nouvel enregistrement A.
     * @return array Le tableau de réponse de l'API contenant les détails de l'enregistrement créé.
     */
    public function create(string $domain, string $ip): array
    {
        return $this->call('post', '/domain/zone/batistack.ovh/record', [
            "fieldType" => "A",
            "subDomain" => $domain,
            "target" => $ip,
            "ttl" => 0
        ]);
    }
}