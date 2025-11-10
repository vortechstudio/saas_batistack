<?php

namespace App\Services\Ovh;

use Ovh\Api;

class OvhWrapper
{
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
