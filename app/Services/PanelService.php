<?php

namespace App\Services;

use CURLFile;
use Exception;

class PanelService
{
    private $apiKey;
    private $baseUrl;
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
    private function generateRequestData()
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
    private function httpPostWithCookie($url, $data, $timeout = 60)
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

    /**
     * Fetch logs from the API.
     *
     * @return array Log data
     */
    public function fetchLogs()
    {
        $url = $this->baseUrl . '/v2/data?action=getData';

        $requestData = $this->generateRequestData();
        $requestData['table'] = 'logs';
        $requestData['limit'] = 10;
        $requestData['tojs'] = 'test';

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Fetch databases from the API.
     *
     * @return array Databases data
     */
    public function fetchDatabases(int $limit = 10, int $page = 1, ?string $search = null)
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

    /**
     * Add a new site.
     *
     * @param string $domain Domain name
     * @param string $path Path to site
     * @param string $runPath Run path
     * @param string $description Description of the site
     * @param int $typeId Type ID
     * @param string $type Type (e.g., php)
     * @param string $phpVersion PHP version
     * @param string $port Port number
     * @param bool|null $ftp FTP required or not
     * @param string|null $ftpUsername FTP username
     * @param string|null $ftpPassword FTP password
     * @param bool|null $sql SQL database required or not
     * @param string|null $databaseUsername Database username
     * @param string|null $databasePassword Database password
     * @param int $setSsl Set SSL or not
     * @param int $forceSsl Force SSL or not
     * @return array Response from the API
     */
    public function addSite($domain, $path, $runPath = null, $typeId = 0, $type = 'php', $phpVersion = '73', $port = '80', $ftp = null, $ftpUsername = null, $ftpPassword = null, $sql = null, $databaseUsername = null, $databasePassword = null, $setSsl = 0, $forceSsl = 0)
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

        if ($ftp !== null) {
            $requestData['ftp'] = $ftp;
            $requestData['ftp_username'] = $ftpUsername;
            $requestData['ftp_password'] = $ftpPassword;
        }

        if ($sql !== null) {
            $requestData['sql'] = $sql;
            $requestData['datauser'] = $databaseUsername;
            $requestData['datapassword'] = $databasePassword;
        }

        $requestData['codeing'] = 'utf8';
        $requestData['version'] = $phpVersion;
        $requestData['type_id'] = $typeId;
        $requestData['set_ssl'] = $setSsl;
        $requestData['force_ssl'] = $forceSsl;
        $requestData['is_create_default_file'] = 'true';

        $result = $this->httpPostWithCookie($url, $requestData);
        $result = json_decode($result, true);

        if($runPath !== null && $result['message']['siteId']) {
            $this->updateRunPath($result['message']['siteId'], $runPath);
        }

        return $result;
    }

    public function updateRunPath($id, $runPath) {
        $url = $this->baseUrl . '/v2/site?action=SetSiteRunPath';

        $requestData = $this->generateRequestData();
        $requestData['id'] = $id;
        $requestData['runPath'] = $runPath;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * check run path.
     *
     * @param string $domain Path to check
     * @return array Response from the API
     */
    public function checkRunPath($domain) {
        $response = $this->fetchSites(limit: 20, page: 1, search: $domain);

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

        $url = $this->baseUrl . '/v2/site?action=GetDirUserINI';

        $requestData = $this->generateRequestData();
        $requestData['id'] = $id;
        $requestData['path'] = $runPath;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Add a subdomain.
     *
     * @param string $subdomain Subdomain name
     * @param string $mainDomain Main domain
     * @param string $ipTarget IP address or target for the subdomain
     * @return array Response from the API
     */
    public function addSubdomain($subdomain, $mainDomain, $ipTarget)
    {
        $url = $this->baseUrl . '/v2/plugin?action=a&name=dns_manager&s=act_resolve';

        $requestData = $this->generateRequestData();
        $requestData['host'] = $subdomain;
        $requestData['value'] = $ipTarget;
        $requestData['domain'] = $mainDomain;
        $requestData['ttl'] = '600';
        $requestData['type'] = 'A';
        $requestData['act'] = 'add';

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Delete a subdomain.
     *
     * @param string $subdomain Subdomain name
     * @param string $mainDomain Main domain
     * @param string $ipTarget IP address or target for the subdomain
     * @return array Response from the API
     */
    public function deleteSubdomain($subdomain, $mainDomain, $ipTarget)
    {
        $url = $this->baseUrl . '/v2/plugin?action=a&name=dns_manager&s=act_resolve';

        $requestData = $this->generateRequestData();
        $requestData['host'] = $subdomain;
        $requestData['value'] = $ipTarget;
        $requestData['domain'] = $mainDomain;
        $requestData['ttl'] = '600';
        $requestData['type'] = 'A';
        $requestData['act'] = 'delete';

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Fetch list of FTP accounts.
     *
     * @return array List of FTP accounts
     */
    public function fetchFtpAccounts($limit, $page, $search = null)
    {
        $url = $this->baseUrl . '/v2/data?action=getData';

        $requestData = $this->generateRequestData();
        $requestData['table'] = 'ftps';
        $requestData['limit'] = $limit;
        $requestData['p'] = $page;
        $requestData['search'] = $search;
        $requestData['type'] = '-1';

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Add a new FTP account.
     *
     * @param string $username FTP username
     * @param string $password FTP password
     * @return array Response from the API
     */
    public function addFtpAccount($username, $password,$path,$ps = null)
    {
        $url = $this->baseUrl . '/v2/ftp?action=AddUser';

        $requestData = $this->generateRequestData();
        $requestData['ftp_username'] = $username;
        $requestData['ftp_password'] = $password;
        $requestData['path'] = $path;
        if(!is_null($ps))
            $requestData['ps'] = $ps;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    public function addDatabase($databaseUsername, $databasePassword)
    {
        $url = $this->baseUrl . '/v2/database?action=AddDatabase';

        $requestData = $this->generateRequestData();
        $requestData['sid'] = 0;
        $requestData['codeing'] = 'utf8';
        $requestData['db_user'] = $databaseUsername;
        $requestData['password'] = $databasePassword;
        $requestData['dataAccess'] = '127.0.0.1';
        $requestData['address'] = '127.0.0.1';
        $requestData['active'] = 'false';
        $requestData['ps'] = $databaseUsername;
        $requestData['dtype'] = 'MySQL';
        $requestData['name'] = $databaseUsername;

        try {
            $result = $this->httpPostWithCookie($url, $requestData);
            return json_decode($result, true);
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Delete an FTP account.
     *
     * @param string $username FTP username
     * @return array Response from the API
     */
    public function deleteFtpAccount($username,$id)
    {
        $url = $this->baseUrl . '/v2/ftp?action=DeleteUser';

        $requestData = $this->generateRequestData();
        $requestData['username'] = $username;
        $requestData['id'] = $id;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }



  /**
     * Import SQL file into a database.
     *
     * @param string $file Path to the SQL file
     * @param string $databaseName Name of the database
     * @return array Response from the API
     */
    public function importSqlFile($file, $databaseName)
    {
        $url = $this->baseUrl . '/v2/database?action=InputSql';

        $requestData = $this->generateRequestData();
        $requestData['file'] = $file;
        $requestData['name'] = $databaseName;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Save file content to a specified path.
     *
     * @param string $fileContent Content of the file
     * @param string $path Path where the file will be saved
     * @return array Response from the API
     */
    public function saveFile($fileContent, $path)
    {
        $url = $this->baseUrl . '/v2/files?action=SaveFileBody';

        $requestData = $this->generateRequestData();
        $requestData['data'] = $fileContent;
        $requestData['path'] = $path;
        $requestData['encoding'] = 'utf-8';

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Unzip a ZIP archive to a specified destination.
     *
     * @param string $sourceFile Path to the ZIP file
     * @param string $destination Path where the contents will be extracted
     * @param string|null $password Password for the ZIP file (optional)
     * @return array Response from the API
     */
    public function unzipFile($sourceFile, $destination, $password = null)
    {
        $url = $this->baseUrl . '/v2/files?action=UnZip';

        $requestData = $this->generateRequestData();
        $requestData['sfile'] = $sourceFile;
        $requestData['dfile'] = $destination;
        $requestData['type'] = 'zip';
        $requestData['coding'] = 'UTF-8';
        $requestData['password'] = $password;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Apply SSL certificate to a domain.
     *
     * @param string $domain Domain name
     * @param int $domainId Domain ID
     * @param int $autoWildcard Automatically apply wildcard SSL (0 or 1)
     * @return array Response from the API
     */
    public function applySslCertificate($domain, $domainId, $autoWildcard = 0)
    {
        $applyCertUrl = $this->baseUrl . '/v2/acme?action=apply_cert_api';
        $setSslUrl = $this->baseUrl . '/v2/site?action=SetSSL';

        // Apply certificate
        $applyCertData = $this->generateRequestData();
        $applyCertData['domains'] = '["' . $domain . '"]';
        $applyCertData['id'] = $domainId;
        $applyCertData['auth_to'] = $domainId;
        $applyCertData['auth_type'] = 'http';
        $applyCertData['auto_wildcard'] = $autoWildcard;

        $applyCertResult = $this->httpPostWithCookie($applyCertUrl, $applyCertData);
        $result = json_decode($applyCertResult, true);

        // Set SSL
        $setSslData = $this->generateRequestData();
        $setSslData['type'] = '1';
        $setSslData['siteName'] = $domain;
        $setSslData['key'] = $result['private_key'];
        $setSslData['csr'] = $result['cert'] . ' ' . $result['root'];

        $setSslResult = $this->httpPostWithCookie($setSslUrl, $setSslData);

        return json_decode($setSslResult, true);
    }

    /**
     * Disable SSL for a domain.
     *
     * @param string $domain Domain name
     * @return array Disable SSL response as associative array, null on failure
     */
    public function disableSsl($domain)
    {
        $disableSsl = $this->baseUrl . '/v2/site?action=CloseSSLConf';

        // Set SSL
        $disableSslData = $this->generateRequestData();
        $disableSslData['updateOf'] = '1';
        $disableSslData['siteName'] = $domain;

        $setSslResult = $this->httpPostWithCookie($disableSsl, $disableSslData);

        return json_decode($setSslResult, true);
    }

     /**
     * Upload key and cert SSL certificate for a domain if available from your computer.
     *
     * @param string $domain Domain name
     * @param string $key Key SSL certificate
     * @param string $cert Cert SSL certificate
     * @return array Upload response as associative array, null on failure
     */
    public function uploadCert($domain, $key, $cert) {
        // API endpoint URL for certificate save
        $urlUpload = $this->baseUrl . '/v2/ssl_domain?action=upload_cert';

        // Prepare request data
        $requestUpload = $this->generateRequestData();
        $requestUpload['key'] = $key;
        $requestUpload['cert'] = $cert;

        // Make POST request with cookie authentication
        $resultUpload = $this->httpPostWithCookie($urlUpload, $requestUpload);
        $hash = json_decode($resultUpload, true)['message']['hash'];

        // API endpoint URL for certificate save
        $urlDeploy = $this->baseUrl . '/v2/ssl_domain?action=cert_deploy_sites';

        // Prepare request data
        $requestDeploy = $this->generateRequestData();
        $requestDeploy['hash'] = $hash;
        $requestDeploy['domains'] = '["' . $domain . '"]';
        $requestDeploy['append'] = '1';

        // Make POST request with cookie authentication
        $resultDeploy = $this->httpPostWithCookie($urlDeploy, $requestDeploy);

        // Decode JSON response
        return [
            'upload' => json_decode($resultUpload, true),
            'deploy' => json_decode($resultDeploy, true),
        ];
    }

     /**
     * Renew SSL certificate for a domain.
     *
     * @param string $domain Domain name
     * @return array Renewal response as associative array, null on failure
     */
    public function renewCert($domain) {
        // Get index value for the domain
        $hash = $this->getHashValue($domain);

        // Check if index was retrieved successfully
        if (!$hash) {
            return "hash is not found"; // Return null if hash is not found
        }

        // API endpoint URL for certificate renewal
        $url = $this->baseUrl . '/v2/ssl_domain?action=renew_cert';

        // Prepare request data
        $requestData = $this->generateRequestData();
        $requestData['hash'] = $hash;

        // Make POST request with cookie authentication
        $resultRenew = $this->httpPostWithCookie($url, $requestData);

        // API endpoint URL for certificate save
        $urlUpload = $this->baseUrl . '/v2/ssl_domain?action=upload_cert';

        $sslDetails = $this->getSSLDetails($domain);

        // Prepare request data
        $requestUpload = $this->generateRequestData();
        $requestUpload['key'] = $sslDetails['key'];
        $requestUpload['cert'] = $sslDetails['cert'];

        // Make POST request with cookie authentication
        $resultUpload = $this->httpPostWithCookie($urlUpload, $requestUpload);

        // API endpoint URL for certificate save
        $urlDeploy = $this->baseUrl . '/v2/ssl_domain?action=cert_deploy_sites';

        // Prepare request data
        $requestDeploy = $this->generateRequestData();
        $requestDeploy['hash'] = $hash;
        $requestDeploy['domains'] = '["' . $domain . '"]';
        $requestDeploy['append'] = '1';

        // Make POST request with cookie authentication
        $resultDeploy = $this->httpPostWithCookie($urlDeploy, $requestDeploy);

        // Decode JSON response
        return [
            'renew' => json_decode($resultRenew, true),
            'upload' => json_decode($resultUpload, true),
            'deploy' => json_decode($resultDeploy, true),
        ];
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

    /**
     * Get SSL details for a domain and return the 'ssl' value.
     *
     * @param string $domain Domain name
     * @return array Response from the API
     */
    public function getSSLDetails($domain) {
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
        if ($response && isset($response['message']['key']) && isset($response['message']['csr'])) {
            // return $response;
            return [
                'key' => $response['message']['key'],
                'cert' => $response['message']['csr'],
                'other' => $response['message'],
            ];
        }

        return null;
    }

    /**
     * Enable HTTPS redirection for a site.
     *
     * @param string $siteName Name of the site
     * @return array Response from the API
     */
    public function enableHttpsRedirection($siteName)
    {
        $url = $this->baseUrl . '/v2/site?action=HttpToHttps';

        $requestData = $this->generateRequestData();
        $requestData['siteName'] = $siteName;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Disable a site.
     *
     * @param int $siteId ID of the site
     * @param string $siteName Name of the site
     * @return array Response from the API
     */
    public function disableSite($siteId, $siteName)
    {
        $url = $this->baseUrl . '/v2/site?action=SiteStop';

        $requestData = $this->generateRequestData();
        $requestData['id'] = $siteId;
        $requestData['name'] = $siteName;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Enable a site.
     *
     * @param int $siteId ID of the site
     * @param string $siteName Name of the site
     * @return array Response from the API
     */
    public function enableSite($siteId, $siteName)
    {
        $url = $this->baseUrl . '/v2/site?action=SiteStart';

        $requestData = $this->generateRequestData();
        $requestData['id'] = $siteId;
        $requestData['name'] = $siteName;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Retrieve details of a specific FTP account.
     *
     * @param string $username FTP username
     * @return array Response from the API
     */
    public function getFtpAccountDetails($username)
    {
        $url = $this->baseUrl . '/v2/ftp?action=GetUser';

        $requestData = $this->generateRequestData();
        $requestData['user'] = $username;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Set server configuration parameters.
     *
     * @param array $configData Configuration data
     * @return array Response from the API
     */
    public function setServerConfig($configData)
    {
        $url = $this->baseUrl . '/v2/server?action=setConfig';

        $requestData = $this->generateRequestData();
        $requestData['config'] = json_encode($configData);

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Get server configuration parameters.
     *
     * @return array Response from the API
     */
    public function getServerConfig()
    {
        $url = $this->baseUrl . '/v2/server?action=getConfig';

        $requestData = $this->generateRequestData();

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }



    /**
     * Delete Site
     *
     * @return array Response from the API
     */
    public function deleteSite($domain,$id)
    {
        $url = $this->baseUrl . '/v2/site?action=DeleteSite';

        $requestData = $this->generateRequestData();

        $requestData['id'] = $id;
        $requestData['webname'] = $domain;
        // $requestData['ftp'] = "1";
        // $requestData['database'] = "1";
        // $requestData['path'] = "1";

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Site List
     *
     * @return array Response from the API
     */
    public function fetchSites($limit = 10, $page = 1, $search = null)
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

    /**
     * Fetch Dir
     *
     * @return array Response from the API
     */
    public function fetchDirectory($path, $page, $showRow = 100)
    {
        $url = $this->baseUrl . '/v2/files?action=GetDir';

        $requestData = $this->generateRequestData();
        $requestData['path'] = $path;
        $requestData['p'] = $page;
        $requestData['showRow'] =  $showRow ;
        $requestData['is_operating'] =  true;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }


    /**
     * Download Remote File
     *
     * @return array Response from the API
     */
    public function downloadFile($url,$path, $filename)
    {
        $url = $this->baseUrl . '/v2/files?action=DownloadFile';

        $requestData = $this->generateRequestData();
        $requestData['url'] = $url;
        $requestData['path'] = $path;
        $requestData['filename'] = $filename;
        $result = $this->httpPostWithCookie($url, $requestData);
        return json_decode($result, true);
    }


    /**
     * Retrive File Content
     *
     * @return array Response from the API
     */
    public function getFileBody($path)
    {
        $url = $this->baseUrl . '/v2/files?action=GetFileBody';

        $requestData = $this->generateRequestData();
        $requestData['path'] = $path;
        $result = $this->httpPostWithCookie($url, $requestData);
        return json_decode($result, true);
    }

    /**
     * Upload File
     *
     * @return array Response from the API
     */
    public function uploadFile($localPath,$path, $filename)
    {
        $url = $this->baseUrl . '/v2/files?action=DownloadFile';

        $filesize = filesize($localPath);

        $requestData = $this->generateRequestData();
        $requestData['f_path'] = $path;
        $requestData['f_name'] = $filename;
        $requestData['f_size'] = $filesize;
        $requestData['f_start'] = 0;
        $requestData['blob'] = new CURLFile($localPath, mime_content_type($localPath), $filename);

        $result = $this->httpPostWithCookie($url, $requestData);
        return json_decode($result, true);
    }
}
