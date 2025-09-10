<?php

namespace App\Services\AaPanel;

use Exception;

class DatabaseService extends PanelService
{
    public function add(string $databaseUsername, string $databasePassword)
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

    public function delete(string $nameDatabase)
    {
        $fetch = new FetchService();
        $database = $fetch->databases(1,1, $nameDatabase);

        $url = $this->baseUrl . '/v2/database?action=DeleteDatabase';

        $requestData = $this->generateRequestData();
        $requestData['id'] = $database['message']['data'][0]['id'];
        $requestData['name'] = $nameDatabase;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    public function backupsList(string $nameDatabase)
    {
        $fetch = new FetchService();
        $database = $fetch->databases(1,1, $nameDatabase);

        $url = $this->baseUrl . '/v2/data?action=getData';

        $requestData = $this->generateRequestData();
        $requestData['table'] = 'backup';
        $requestData['limit'] = 10;
        $requestData['p'] = 1;
        $requestData['search'] = $database['message']['data'][0]['id'];
        $requestData['type'] = 1;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true)['message']['data'];
    }

    public function addBackup(string $nameDatabase)
    {
        $fetch = new FetchService();
        $database = $fetch->databases(1,1, $nameDatabase);
        $url = $this->baseUrl . '/v2/database?action=ToBackup';

        $requestData = $this->generateRequestData();
        $requestData['id'] = $database['message']['data'][0]['id'];

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    public function restoreBackup(string $nameDatabase, string $backupFile)
    {
        $url = $this->baseUrl . '/v2/database?action=InputSql';

        $requestData = $this->generateRequestData();
        $requestData['name'] = $nameDatabase;
        $requestData['file'] = $backupFile;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    public function deleteBackup(int $backupId)
    {
        $url = $this->baseUrl . '/v2/database?action=DelBackup';

        $requestData = $this->generateRequestData();
        $requestData['id'] = $backupId;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }
}
