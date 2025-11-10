<?php

namespace App\Services;

class Forge
{
    public function getIpAddressServer()
    {
        $forge = new \Laravel\Forge\Forge(config('batistack.forge_api_key'));
        return collect($forge->servers())->first()->ipAddress;
    }
}
