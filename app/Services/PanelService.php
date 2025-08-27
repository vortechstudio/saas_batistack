<?php

namespace App\Services;

use AlfikriPayakumbuh\AAPanelAPI\aaPanelApiClient;

class PanelService
{
    public $client;

    public function __construct()
    {
        $this->client = new aaPanelApiClient(
            config('batistack.aapanel.api_key'),
            config('batistack.aapanel.endpoint'),
            './'
        );
    }
}
