<?php

namespace App\Services\AaPanel;

class FetchService extends PanelService
{
    public function logs()
    {
        dd($this->baseUrl);
        $url = $this->baseUrl . '/v2/data?action=getData';

        $requestData = $this->generateRequestData();
        $requestData['table'] = 'logs';
        $requestData['limit'] = 10;
        $requestData['tojs'] = 'test';

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    public function databases(int $limit = 10, int $page = 1, ?string $search = null)
    {
        $url = $this->baseUrl . '/v2/data?action=getData';

        $requestData = $this->generateRequestData();
        $requestData['table'] = 'databases';
        $requestData['limit'] = $limit;
        $requestData['p'] = $page;
        $requestData['search'] = $search;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    public function sites(int $limit = 10, int $page = 1, ?string $search = null)
    {
        $url = $this->baseUrl . '/v2/data?action=getData';

        $requestData = $this->generateRequestData();
        $requestData['table'] = 'sites';
        $requestData['limit'] = $limit;
        $requestData['p'] = $page;
        $requestData['search'] = $search;
        $requestData['type'] = '-1';

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);

    }
}
