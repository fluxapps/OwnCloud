<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class ShareAPI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ownclShareAPI
{

    const URI_SHARE_API = 'ocs/v1.php/apps/files_sharing/api/v1/shares';
    const FORMAT_PARAMETER = '?format=json';
    const SHARE_TYPE_USER = 0;
    const SHARE_TYPE_GROUP = 1;
    const SHARE_TYPE_PUBLIC_LINK = 3;
    const SHARE_TYPE_FEDERATED_CLOUD_SHARE = 6;
    const PERM_TYPE_READ = 1;
    const PERM_TYPE_UPDATE = 2;
    const PERM_TYPE_CREATE = 4;
    const PERM_TYPE_DELETE = 8;
    const PERM_TYPE_READ_WRITE = 15;
    const PERM_TYPE_SHARE = 16;
    const PERM_TYPE_ALL = 31;
    /**
     * @var Client
     */
    protected $http_client;
    /**
     * @var ownclOAuth2UserToken
     */
    protected $token;


    /**
     * ownclShareAPI constructor.
     *
     * @param Client               $http_client
     * @param ownclOAuth2UserToken $token
     */
    public function __construct(Client $http_client, ownclOAuth2UserToken $token)
    {
        $this->http_client = $http_client;
        $this->token = $token;
    }


    /**
     * @return stdClass
     * @throws GuzzleException
     */
    public function all()
    {
        $response = $this->http_client->request('GET', self::URI_SHARE_API . self::FORMAT_PARAMETER, $this->getOptions());

        return json_decode($response->getBody()->getContents());
    }


    /**
     * @param $path
     * @param $user
     *
     * @return mixed
     * @throws GuzzleException
     */
    public function create($path, $user)
    {
        $additional_options = [
            'form_params' => [
                'path'        => $path,
                'shareType'   => self::SHARE_TYPE_USER,
                'shareWith'   => $user,
                'permissions' => self::PERM_TYPE_UPDATE
            ]
        ];
        $response = $this->http_client->request('POST', self::URI_SHARE_API . self::FORMAT_PARAMETER, $this->getOptions($additional_options));

        return json_decode($response->getBody()->getContents());
    }


    /**
     * @param array $additional_options
     *
     * @return array
     */
    protected function getOptions($additional_options = [])
    {
        return array_merge([
            'headers' => ['Authorization' => 'Bearer ' . $this->token->getAccessToken()]
        ], $additional_options);
    }
}