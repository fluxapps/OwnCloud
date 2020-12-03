<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class RESTClient
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ownclRESTClient
{

    /**
     * @var Client
     */
    protected $http_client;
    /**
     * @var ownclConfig
     */
    protected $config;


    /**
     * RESTClient constructor.
     *
     * @param ownclConfig $config
     *
     * @throws ilCloudPluginConfigException
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->http_client = new Client([
            'base_uri' => rtrim($config->getServerURL(), '/')
        ]);
    }


    /**
     * @param ownclAuth $ownclAuth
     * @return ownclShareAPI
     */
    public function shareAPI($ownclAuth)
    {
        return new ownclShareAPI($this->http_client, $ownclAuth);
    }


    /**
     * @param string $uri
     *
     * @return mixed|ResponseInterface
     * @throws GuzzleException
     */
    public function get($uri)
    {
        $response = $this->http_client->request('GET', $uri);

        return $response;
    }
}
