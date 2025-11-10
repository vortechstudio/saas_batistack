<?php

namespace App\Services\Ovh;

use App\Services\Ovh\OvhWrapper;

class Domain extends OvhWrapper
{
    public function list()
    {
        return $this->call('get', '/domain');
    }

    public function create(string $domain, string $ip)
    {
        return $this->call('post', '/domain/zone/batistack.ovh/record', [
            "fieldType" => "A",
            "subDomain" => $domain,
            "target" => $ip,
            "ttl" => 0
        ]);
    }
}
