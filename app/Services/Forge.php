<?php

namespace App\Services;

class Forge
{
    public \Laravel\Forge\Forge $client;

    public function __construct()
    {
        $this->client = new \Laravel\Forge\Forge(config('batistack.forge_api_key'));
    }
    public function getIpAddressServer()
    {
        $forge = new \Laravel\Forge\Forge(config('batistack.forge_api_key'));
        return collect($forge->servers())->first()->ipAddress;
    }
}
