<?php

namespace App\Services\AaPanel;

class CertificateService extends PanelService
{
    public function certDeploySite(array $domains, string $key, string $cert)
    {
        $upload = $this->uploadCertificate($key, $cert);

        $url = $this->baseUrl . '/v2/ssl_domain?action=cert_deploy_sites';

        $requestData = $this->generateRequestData();
        $requestData['hash'] = $upload['message']['hash'];
        $requestData['domains'] = $domains;
        $requestData['append'] = "1";

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    public function forceHttps(string $domain)
    {
        $url = $this->baseUrl . '/v2/site?action=HttpToHttps';

        $requestData = $this->generateRequestData();
        $requestData['siteName'] = $domain;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    private function uploadCertificate(string $key, string $cert)
    {
        $url = $this->baseUrl . '/v2/ssl_domain?action=upload_cert';

        $requestData = $this->generateRequestData();
        $requestData['key'] = $key;
        $requestData['cert'] = $cert;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Get SSL details for a domain and return the 'hash' value.
     *
     * @param string $domain Domain name
     * @return string|null 'hash' value if found, null if not found or on error
     */
    public function getHashValue($domain) {
        // API endpoint URL to fetch SSL details
        $url = $this->baseUrl . '/v2/site?action=GetSSL';

        // Prepare request data
        $requestData = $this->generateRequestData();
        $requestData['siteName'] = $domain;

        // Make POST request with cookie authentication
        $result = $this->httpPostWithCookie($url, $requestData);

        // Decode JSON response
        $response = json_decode($result, true);

        // Check if response is valid and contains 'index' key
        if ($response && isset($response['message']['hash'])) {
            return $response['message']['hash']; // Return 'hash' value
        }

        return null; // Return null if 'index' key is not found or on error
    }
}
