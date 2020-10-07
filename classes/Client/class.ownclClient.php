<?php

use GuzzleHttp\Exception\GuzzleException;

/**
 * Class ownclClient
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ownclClient
{

    const AUTH_BEARER = 'auth_bearer';
    /**
     * @var DAVClient
     */
    protected $sabre_client;
    /**
     * @var ownclRESTClient
     */
    protected $rest_client;
    /**
     * @var ownclApp
     */
    protected $owncl_app;
    /**
     * @var ilOwnCloud
     */
    protected $pl;
    /**
     * @var ownclConfig
     */
    protected $config;
    const DEBUG = true;


    /**
     * @param ownclApp $ownclApp
     */
    public function __construct(ownclApp $ownclApp)
    {
        $this->setOwnCloudApp($ownclApp);
        $this->pl = ilOwnCloudPlugin::getInstance();
        $this->config = new ownclConfig();
    }


    /**
     * @return ownclAuth
     */
    protected function getAuth()
    {
        return $this->getOwnCloudApp()->getOwnclAuth();
    }


    /**
     * @return DAVClient
     */
    protected function getWebDAVClient()
    {
        if (!$this->sabre_client) {
            $this->sabre_client = new DAVClient($this->getAuth()->getClientSettings());
        }

        return $this->sabre_client;
    }


    /**
     * @return ownclRESTClient
     * @throws ilCloudPluginConfigException
     */
    protected function getRESTClient()
    {
        if (!$this->rest_client) {
            $this->rest_client = new ownclRESTClient($this->config);
        }

        return $this->rest_client;
    }


    public function hasConnection()
    {
        try {   //sabredav version 1.8 throws exception on missing connection
            $response = $this->getWebDAVClient()->request('GET', '', null, $this->getAuth()->getHeaders());
        } catch (Exception $e) {
            return false;
        }

        return ($response['statusCode'] < 400);
    }


    /**
     * @return bool
     */
    public function getHTTPStatus()
    {
        try {   //sabredav version 1.8 throws exception on missing connection
            $response = $this->getWebDAVClient()->request('GET', '', null, $this->getAuth()->getHeaders());
        } catch (Exception $e) {
            ownclLog::getInstance()->write('Exception: Building connection to OwnCloud server failed with message: ' . $e->getMessage());

            return false;
        }

        return $response['statusCode'];
    }


    /**
     * @param $id
     *
     * @return ownclFile[]|ownclFolder[]
     */
    public function listFolder($id)
    {
        global $ilLog;
        $id = $this->urlencode(ltrim($id, '/'));
        $ilLog->write('listFolder: ' . $id);

        $settings = $this->getAuth()->getClientSettings();
        if ($client = $this->getWebDAVClient()) {
            $ilLog->write('listFolder: ' . $settings['baseUri'] . $id);

            $response = $client->propFind(
                $settings['baseUri'] . $id,
                [
                    '{http://owncloud.org/ns}id',
                    '{http://owncloud.org/ns}fileid',
                    '{DAV:}getcontenttype',
                    '{DAV:}getcontentlength',
                    '{DAV:}getlastmodified',
                    '{DAV:}getetag'
                ],
                1,
                $this->getAuth()->getHeaders()
            );
            // $response = $client->propFind($settings['baseUri'] . $id, [], 1, $this->getAuth()->getHeaders());
            $items = ownclItemFactory::getInstancesFromResponse($response);

            return $items;
        }

        return array();
    }


    /**
     * @param $path
     *
     * @return bool
     */
    public function folderExists($path)
    {
        return $this->itemExists($path);
    }


    /**
     * @param $path
     *
     * @return bool
     */
    public function fileExists($path)
    {
        return $this->itemExists($path);
    }


    /**
     * @param $path
     *
     * @return ownclFile
     * @throws ilCloudException
     */
    public function deliverFile($path)
    {
        $path = ltrim($path, "/");
        $encoded_path = $this->urlencode($path);

        $headers = $this->getAuth()->getHeaders();

        $settings = $this->getAuth()->getClientSettings();
        $prop = array_shift($this->getWebDAVClient()->propFind($settings['baseUri'] . $encoded_path, array(), 1, $headers));

        header("Content-type: " . $prop['{DAV:}getcontenttype']);
        header("Content-Length: " . $prop['{DAV:}getcontentlength']);
        header("Connection: close");
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');

        set_time_limit(0);

        $opts = array(
            'http' => array(
                'protocol_version' => 1.1,
                'method' => "GET",
                'header' => "Authorization: " . $headers['Authorization']
            )
        );

        $context = stream_context_create($opts);
        $file = &fopen($settings['baseUri'] . $encoded_path, "rb", false, $context);
        fpassthru($file);
        exit();
    }


    /**
     * @param $path
     *
     * @return bool
     */
    public function createFolder($path)
    {
        $path = $this->urlencode($path);
        $response = $this->getWebDAVClient()->request('MKCOL', ltrim($path, '/'), null, $this->getAuth()->getHeaders());
        if (self::DEBUG) {
            global $log;
            $log->write("[ownclClient]->createFolder({$path}) | response status Code: {$response['statusCode']}");
        }

        return ($response['statusCode'] == 200);
    }


    /**
     * urlencode without encoding slashes
     *
     * @param $str
     *
     * @return mixed
     */
    protected function urlencode($str)
    {
        return str_replace('%2F', '/', rawurlencode($str));
    }


    /**
     * @param $location
     * @param $local_file_path
     *
     * @return bool
     * @throws ilCloudException
     */
    public function uploadFile($location, $local_file_path)
    {
        $location = $this->urlencode(ltrim($location, '/'));
        if ($this->fileExists($location)) {
            $basename = pathinfo($location, PATHINFO_FILENAME);
            $extension = pathinfo($location, PATHINFO_EXTENSION);
            $i = 1;
            while ($this->fileExists($basename . "({$i})." . $extension)) {
                $i++;
            }
            $location = $basename . "({$i})." . $extension;
        }
        $response = $this->getWebDAVClient()->request('PUT', $location, file_get_contents($local_file_path), $this->getAuth()->getHeaders());
        if (self::DEBUG) {
            global $log;
            $log->write("[ownclClient]->uploadFile({$location}, {$local_file_path}) | response status Code: {$response['statusCode']}");
        }

        return ($response['statusCode'] == 200);
    }


    /**
     * @param $path
     *
     * @return bool
     */
    public function delete($path)
    {
        $response = $this->getWebDAVClient()->request('DELETE', ltrim($this->urlencode($path), '/'), null, $this->getAuth()->getHeaders());
        if (self::DEBUG) {
            global $log;
            $log->write("[ownclClient]->delete({$path}) | response status Code: {$response['statusCode']}");
        }

        return ($response['statusCode'] == 200);
    }


    /**
     * @param $path
     *
     * @return bool
     */
    protected function itemExists($path)
    {
        try {
            $request = $this->getWebDAVClient()->request('GET', ltrim($this->urlencode($path), '/'), null, $this->getAuth()->getHeaders());
        } catch (Exception $e) {
            return false;
        }

        return ($request['statusCode'] < 400);
    }


    /**
     * @return ownclApp
     */
    public function getOwnCloudApp()
    {
        return $this->owncl_app;
    }


    /**
     * @param $owncl_app
     */
    public function setOwnCloudApp($owncl_app)
    {
        $this->owncl_app = $owncl_app;
    }


    /**
     * (re)initialize the client with settings from the owncloud object
     */
    public function loadClient()
    {
        $this->sabre_client = new DAVClient($this->getAuth()->getClientSettings());
    }


    /**
     * @param string    $path
     * @param ilObjUser $user
     *
     * @throws ilCloudPluginConfigException
     * @throws GuzzleException
     */
    public function shareItem($path, $user)
    {
        if ($user->getId() == $this->getOwnCloudApp()->getIlOwnCloud()->getOwnerId()) {
            // no need to share with yourself (can result in an error with nextcloud)
            return;
        }
        $token = ownclOAuth2UserToken::getUserToken($this->getOwnCloudApp()->getIlOwnCloud()->getOwnerId());
        if ($token) {
            $user_string = $this->config->getMappingValueForUser($user);
            $existing = $this->getRESTClient()->shareAPI($token)->getForPath($path);
            foreach ($existing as $share) {
                if ($share->getShareWith() === $user_string) {
                    if (!$share->hasPermission(ownclShareAPI::PERM_TYPE_UPDATE)) {
                        $this->getRESTClient()->shareAPI($token)->update($share->getId(), $share->getPermissions() | (ownclShareAPI::PERM_TYPE_UPDATE + ownclShareAPI::PERM_TYPE_READ));
                    }
                    return;
                }
            }
            $this->getRESTClient()->shareAPI($token)->create($path, $user_string, ownclShareAPI::PERM_TYPE_UPDATE + ownclShareAPI::PERM_TYPE_READ);
        }
    }


    /**
     * @param string $path
     *
     * @return string
     */
    public function pathToId(string $path) : string
    {
        $settings = $this->getAuth()->getClientSettings();

        $client = $this->getWebDAVClient();

        $response = $client->propFind(
            $settings['baseUri'] . $path,
            [
                '{http://owncloud.org/ns}fileid'
            ],
            0,
            $this->getAuth()->getHeaders()
        );

        $id = strval(current($response));

        return $id;
    }
}
