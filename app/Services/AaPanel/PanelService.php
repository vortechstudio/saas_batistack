<?php

namespace App\Services\AaPanel;

class PanelService
{
    private $apiKey;
    public $baseUrl;
    private $pathCookie;

    public function __construct()
    {
        $this->apiKey  = config('batistack.aapanel.api_key');
        $this->baseUrl = config('batistack.aapanel.endpoint');
        $this->pathCookie = $pathCookie ?? './';
    }

    /**
     * Generate request token and time.
     *
     * @return array Request token and time
     */
    protected function generateRequestData()
    {
        return [
            'request_token' => md5(time() . md5($this->apiKey)),
            'request_time' => time(),
        ];
    }

    /**
     * Perform HTTP POST request with cookie handling.
     *
     * @param string $url URL of the API endpoint
     * @param array $data Data to send with the request
     * @param int $timeout Timeout for the request
     * @return mixed Response from the API
     */
    protected function httpPostWithCookie($url, $data, $timeout = 60)
    {
        $cookieFile = $this->pathCookie . md5($this->baseUrl) . '.cookie';
        if (!file_exists($cookieFile)) {
            $fp = fopen($cookieFile, 'w+');
            fclose($fp);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }
}
