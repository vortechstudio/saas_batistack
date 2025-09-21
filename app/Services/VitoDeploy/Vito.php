<?php

namespace App\Services\VitoDeploy;

use Illuminate\Support\Facades\Http;

class Vito
{
    private $apiKey;
    private $endpoint;

    public function __construct()
    {
        $this->apiKey = config('services.vito.api_key');
        $this->endpoint = config('services.vito.endpoint');
    }

    public function get($path, $data = [])
    {
        $url = $this->endpoint . $path;
        $request = Http::withoutVerifying()
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->withToken($this->apiKey)
            ->get($url, $data);

        return $request->json();
    }

    public function post($path, $data = [])
    {
        $url = $this->endpoint . $path;
        $request = Http::withoutVerifying()
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->withToken($this->apiKey)
            ->post($url, $data);

        return $request->json();
    }
}
