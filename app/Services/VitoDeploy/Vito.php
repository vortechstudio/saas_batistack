<?php

namespace App\Services\VitoDeploy;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class Vito
{
    private string $apiKey;
    private string $endpoint;

    public function __construct()
    {
        $apiKey = config('services.vito.api_key');
        $endpoint = config('services.vito.endpoint');

        // Validation de l'API key
        if (empty($apiKey) || !is_string($apiKey)) {
            throw new InvalidArgumentException(
                'La clé API Vito est requise et doit être une chaîne non vide. ' .
                'Vérifiez la configuration services.vito.api_key.'
            );
        }

        // Validation de l'endpoint
        if (empty($endpoint) || !is_string($endpoint)) {
            throw new InvalidArgumentException(
                'L\'endpoint Vito est requis et doit être une chaîne non vide. ' .
                'Vérifiez la configuration services.vito.endpoint.'
            );
        }

        // Validation du format de l'endpoint (doit être une URL valide)
        if (!filter_var($endpoint, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(
                'L\'endpoint Vito doit être une URL valide. ' .
                'Valeur fournie: ' . $endpoint
            );
        }

        // Assignation des propriétés après validation
        $this->apiKey = $apiKey;
        $this->endpoint = rtrim($endpoint, '/'); // Supprimer le slash final si présent
    }

    public function get(string $path, array $data = []): array
    {
        $url = $this->endpoint . '/' . ltrim($path, '/');
        $client = Http::when(!app()->isLocal() && !app()->runningUnitTests(), fn ($http) => $http, fn ($http) => $http->withoutVerifying())
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->withToken($this->apiKey)
            ->timeout(15)
            ->retry(2, 200);
        $response = $client->get($url, $data)->throw();
        return (array) $response->json();
    }

    public function post($path, $data = [])
    {
        $url = $this->endpoint . '/' . ltrim($path, '/');
        $client = Http::when(!app()->isLocal() && !app()->runningUnitTests(), fn ($http) => $http, fn ($http) => $http->withoutVerifying())
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->withToken($this->apiKey)
            ->timeout(15)
            ->retry(2, 200);
        $response = $client->post($url, $data)->throw();
        return (array) $response->json();
    }
}
