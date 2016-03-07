<?php
require_once(dirname(__DIR__) . '/Client/class.ownclClient.php');

/**
 * Class ownclApp
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ownclApp {

	const SSL_STANDARD = NULL;
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
	 * @var int
	 */
	protected $il_own_cloud;
	/**
	 * @var
	 */
	protected $ssl_version = self::SSL_STANDARD;
	/**
	 * @var
	 */
	protected static $instance;


	protected function __construct() {
		$ownclClient = new ownclClient($this);
		$this->setOwnCloudlClient($ownclClient);
	}


	/**
	 * @return ownclApp
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	public function buildURLs() {
	}


	/**
	 * @return mixed|string
	 */
	public function getHttpPath() {
		$http_path = ILIAS_HTTP_PATH;
		if (substr($http_path, - 1, 1) != '/') {
			$http_path = $http_path . '/';
		}
		if (strpos($http_path, 'Customizing') > 0) {
			$http_path = strstr($http_path, 'Customizing', true);
		}

		return $http_path;
	}


	/**
	 * @param ilOwnCloud $il_own_cloud
	 */
	public function setOwnCloud(ilOwnCloud $il_own_cloud) {
		$this->il_own_cloud = $il_own_cloud;
	}


	/**
	 * @return ownclClient
	 */
	public function getOwnCloudClient() {
		return $this->owncl_client;
	}


	/**
	 * @param ownclClient $owncl_client
	 */
	public function setOwnCloudlClient($owncl_client) {
		$this->owncl_client = $owncl_client;
	}


	/**
	 * @return string
	 */
	public function getBaseUrl() {
		return $this->base_url;
	}


	/**
	 * @param string $base_url
	 */
	public function setBaseUrl($base_url) {
		$this->base_url = $base_url;
	}


	/**
	 * @return string
	 */
	public function getRessourceUri() {
		return $this->ressource_uri;
	}


	/**
	 * @param string $ressource_uri
	 */
	public function setRessourceUri($ressource_uri) {
		$this->ressource_uri = $ressource_uri;
	}


	/**
	 * @return string
	 */
	public function getRessource() {
		return $this->ressource;
	}


	/**
	 * @param string $ressource
	 */
	public function setRessource($ressource) {
		$this->ressource = $ressource;
	}
}


