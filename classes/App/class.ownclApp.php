<?php

/**
 * Class ownclApp
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ownclApp
{

    const SSL_STANDARD = null;
    const SSL_V3 = 3;
    const RESP_TYPE_CODE = 'code';
    /**
     * @var string
     */

    protected $base_url = '';
    /**
     * @var string
     */
    protected $token_url = '';
    /**
     * @var string
     */
    protected $client_id = '';
    /**
     * @var string
     */
    protected $response_type = self::RESP_TYPE_CODE;
    /**
     * @var string
     */
    protected $client_secret = '';
    /**
     * @var string
     */
    protected $ressource_uri = '';
    /**
     * @var string
     */
    protected $ressource = '';
    /**
     * @var ownclClient
     */
    protected $owncl_client;
    /**
     * @var ownclAuth
     */
    protected $owncl_auth;
    /**
     * @var ilOwnCloud
     */
    protected $ilOwnCloud;
    /**
     * @var
     */
    protected $ssl_version = self::SSL_STANDARD;
    /**
     * @var
     */
    protected static $instance;


    protected function __construct()
    {
        $ownclClient = new ownclClient($this);
        $this->setOwnCloudClient($ownclClient);
        $this->initAuth();
    }


    protected function initAuth()
    {
        $config = new ownclConfig();
        if ($config->getOAuth2Active()) {
            require_once 'Customizing/global/plugins/Modules/Cloud/CloudHook/OwnCloud/classes/Auth/class.ownclAuthOAuth2.php';
            $this->owncl_auth = new ownclAuthOAuth2($this);
        } else {
            require_once 'Customizing/global/plugins/Modules/Cloud/CloudHook/OwnCloud/classes/Auth/class.ownclAuthBasic.php';
            $this->owncl_auth = new ownclAuthBasic($this);
        }
    }


    /**
     * @return ownclApp
     */
    public static function getInstance($ilOwnCloud = null)
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($ilOwnCloud);
        }
        self::$instance->setOwnCloud($ilOwnCloud);

        return self::$instance;
    }


    public function buildURLs()
    {
    }


    /**
     * @return mixed|string
     */
    public function getHttpPath()
    {
        $http_path = ILIAS_HTTP_PATH;
        if (substr($http_path, -1, 1) != '/') {
            $http_path = $http_path . '/';
        }
        if (strpos($http_path, 'Customizing') > 0) {
            $http_path = strstr($http_path, 'Customizing', true);
        }

        return $http_path;
    }


    /**
     * @param ilOwnCloud|null $il_own_cloud
     */
    public function setOwnCloud($il_own_cloud)
    {
        $this->ilOwnCloud = $il_own_cloud;
    }


    /**
     * @return ilOwnCloud
     */
    public function getIlOwnCloud()
    {
        return $this->ilOwnCloud;
    }


    /**
     * @return ownclClient
     */
    public function getOwnCloudClient()
    {
        return $this->owncl_client;
    }


    /**
     * @param ownclClient $owncl_client
     */
    public function setOwnCloudClient($owncl_client)
    {
        $this->owncl_client = $owncl_client;
    }


    /**
     * @return ownclAuth
     */
    public function getOwnclAuth()
    {
        return $this->owncl_auth;
    }


    /**
     * @param ownclAuth $owncl_auth
     */
    public function setOwnclAuth($owncl_auth)
    {
        $this->owncl_auth = $owncl_auth;
    }


    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->base_url;
    }


    /**
     * @param string $base_url
     */
    public function setBaseUrl($base_url)
    {
        $this->base_url = $base_url;
    }


    /**
     * @return string
     */
    public function getRessourceUri()
    {
        return $this->ressource_uri;
    }


    /**
     * @param string $ressource_uri
     */
    public function setRessourceUri($ressource_uri)
    {
        $this->ressource_uri = $ressource_uri;
    }


    /**
     * @return string
     */
    public function getRessource()
    {
        return $this->ressource;
    }


    /**
     * @param string $ressource
     */
    public function setRessource($ressource)
    {
        $this->ressource = $ressource;
    }
}


