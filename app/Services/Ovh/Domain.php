<?php

namespace App\Services\Ovh;

use App\Services\Ovh\OvhWrapper;

class Domain extends OvhWrapper
{
    public function list(): array
    {
        return $this->call('get', '/domain');
    }

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
