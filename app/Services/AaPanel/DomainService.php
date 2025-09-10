<?php

namespace App\Services\AaPanel;

class DomainService extends PanelService
{
    public function add(string $domain, string $path, ?string $runPath = null, int $typeId = 0, string $type = 'php', string $phpVersion = '73', string $port = '80')
    {
        $url = $this->baseUrl . '/v2/site?action=AddSite';

        $jsonData = [
            'domain' => $domain,
            'domainlist' => [],
            'count' => 0,
        ];

        $requestData = $this->generateRequestData();
        $requestData['webname'] = json_encode($jsonData);
        $requestData['port'] = $port;
        $requestData['type'] = $type;
        $requestData['ps'] = str_replace('.', '_', $domain);
        $requestData['path'] = $path;

        $requestData['codeing'] = 'utf8';
        $requestData['version'] = $phpVersion;
        $requestData['type_id'] = $typeId;
        $requestData['is_create_default_file'] = 'false';

        $result = $this->httpPostWithCookie($url, $requestData);
        $result = json_decode($result, true);

        if($runPath !== null && $result['message']['siteId']) {
            $this->updateRunPath($result['message']['siteId'], $runPath);
        }

        return $result;
    }

    public function updateRunPath(int $id, string $runPath)
    {
        $url = $this->baseUrl . '/v2/site?action=SetSiteRunPath';

        $requestData = $this->generateRequestData();
        $requestData['id'] = $id;
        $requestData['runPath'] = $runPath;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    public function checkRunPath(string $domain)
    {
        $fetch = new FetchService();
        $response = $fetch->sites(
            limit: 20,
            page: 1,
            search: $domain
        );

        if (isset($response['message']['data']) && is_array($response['message']['data']) && $response['message']['data'] != []) {
            foreach ($response['message']['data'] as $item) {
                if (isset($item['name']) && $item['name'] == $domain) {
                    $url = $this->baseUrl . '/v2/site?action=GetDirUserINI';

                    $requestData = $this->generateRequestData();
                    $requestData['id'] = $item['id'];
                    $requestData['path'] = $item['path'];

                    $result = $this->httpPostWithCookie($url, $requestData);

                    return json_decode($result, true);
                }
            }
        } else {
            // Jika tidak ada data, tambahkan entri kosong
            return [
                'message' => 'Website is not found',
                'status' => -1,
            ];
        }
    }

    public function rewriteUrlSite(string $domain, string $data)
    {
        $url = $this->baseUrl . '/v2/file?action=SaveFileBody';
        $path = '/www/server/panel/vhost/rewrite/'.$domain.'.conf';

        $requestData = $this->generateRequestData();
        $requestData['path'] = $path;
        $requestData['body'] = $data;
        $requestData['encoding'] = 'utf8';

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    public function disabled(string $domain)
    {
        $fetch = new FetchService();
        $response = $fetch->sites(
            limit: 20,
            page: 1,
            search: $domain
        );

        $requestData = $this->generateRequestData();
        $requestData['id'] = $response['message']['data'][0]['id'];
        $requestData['name'] = $domain;

        $url = $this->baseUrl . '/v2/site?action=SiteStop';

        $result = $this->httpPostWithCookie($url, $requestData);
        $result = json_decode($result, true);
    }

    public function enabled(string $domain)
    {
        $fetch = new FetchService();
        $response = $fetch->sites(
            limit: 20,
            page: 1,
            search: $domain
        );

        $requestData = $this->generateRequestData();
        $requestData['id'] = $response['message']['data'][0]['id'];
        $requestData['name'] = $domain;

        $url = $this->baseUrl . '/v2/site?action=SiteStart';

        $result = $this->httpPostWithCookie($url, $requestData);
        $result = json_decode($result, true);
    }

    public function delete(string $domain)
    {
        $fetch = new FetchService();
        $response = $fetch->sites(
            limit: 20,
            page: 1,
            search: $domain
        );

        $requestData = $this->generateRequestData();
        $requestData['id'] = $response['message']['data'][0]['id'];
        $requestData['webname'] = $domain;

        $url = $this->baseUrl . '/v2/site?action=DeleteSite';

        $result = $this->httpPostWithCookie($url, $requestData);
        $result = json_decode($result, true);
    }
}
