<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class NebuloService
{
    private $apiKey;
    private $endpoint;

    public function __construct()
    {
        $this->apiKey = config('batistack.nebulo.api_key');
        $this->endpoint = config('batistack.nebulo.endpoint');
    }

    /**
     * Général
     */

    public function getUser(?User $user = null)
    {
        try {
            if (!$user) {
                $response = Http::withoutVerifying()
                ->withToken($this->apiKey)
                ->get($this->endpoint.'/user');
            } else {
                $apikey = $this->getApiKeyUser($user);
                $response = Http::withoutVerifying()
                ->withToken($apikey)
                ->get($this->endpoint.'/user');
            }
            if($response->status() != 200){
                throw new \Exception('Erreur de récupération de l\'utilisateur');
            }
            return $response->json();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function createUser(User $user)
    {
        try {
            $response = Http::withoutVerifying()
                ->post($this->endpoint.'/register', [
                    'email' => $user->email,
                    'password' => $user->password,
                    'name' => $user->customer->entreprise
                ]);
            if($response->status() != 200){
                throw new \Exception('Erreur de création de l\'utilisateur');
            }
            return $response->json();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getApiKeyUser(User $user)
    {
        try {
            $response = Http::withoutVerifying()
                ->post($this->endpoint.'/login', [
                    'email' => $user->email,
                    'password' => $user->password,
                ]);
            if($response->status() != 200){
                throw new \Exception('Erreur de récupération de la clé API');
            }
            return $response->json()['access_token'];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Gestion des buckets
     */

    public function listBuckets(User $user)
    {
        $apikey = $this->getApiKeyUser($user);

        try {
            $response = Http::withoutVerifying()
                ->withToken($apikey)
                ->get($this->endpoint.'/buckets');
            if($response->status() != 200){
                throw new \Exception('Erreur de récupération des buckets');
            }
            return $response->json();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getBucket(User $user, $bucket)
    {
        $apikey = $this->getApiKeyUser($user);

        try {
            $response = Http::withoutVerifying()
                ->withToken($apikey)
                ->get($this->endpoint.'/buckets/'.$bucket);
            if($response->status() != 200){
                throw new \Exception('Erreur de récupération du bucket');
            }
            return $response->json();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function createBucket($user, string $nameBucket, int $limit_size)
    {
        try {
            $response = Http::withoutVerifying()
                ->withToken($this->apiKey)
                ->post($this->endpoint.'/buckets', [
                    'name' => $nameBucket,
                    'user_id' => $this->getUser($user)['id'],
                    'limit_size' => $limit_size,
                ]);
            if($response->status() != 200){
                throw new \Exception('Erreur de création du bucket');
            }
            return $response->json();
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
