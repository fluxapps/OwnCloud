<?php
require_once('./Modules/Cloud/classes/class.ilCloudPluginConfig.php');
require_once(__DIR__ . '/class.ilOwnCloudPlugin.php');

/**
 * Class ownclConfig
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ownclConfig extends ilCloudPluginConfig {

	const F_BASEURL = 'base_url';
	const F_SERVER_URL = 'server_url';
	const F_WEBDAV_PATH = 'webdav_path';
	const F_DESCRIPTION = 'service_info';
	const F_TITLE = 'service_title';
	const F_OAUTH2_ACTIVE = 'oauth2_active';
	const F_OAUTH2_CLIENT_ID = 'oauth2_client_id';
	const F_OAUTH2_CLIENT_SECRET = 'oauth2_client_secret';
	const F_OAUTH2_PATH = 'oauth2_path';

	const DEFAULT_WEBDAV_PATH = 'remote.php/webdav';
	const DEFAULT_OAUTH2_PATH = 'index.php/apps/oauth2';
	/**
	 * @var ilOwnCloudPlugin
	 */
	protected $pl;


	public function __construct() {
		$this->pl = ilOwnCloudPlugin::getInstance();
		parent::__construct($this->pl->getPluginConfigTableName());
	}


	/**
	 * @var array
	 */
	protected static $value_cache = array();


	/**
	 * @throws ilCloudException
	 */
	public function checkComplete() {

		if (!$this->getServerURL()) {
			throw new ilCloudException(- 1, 'Configuration of OwnCloud incomplete. Please contact your system administrator');
		}

		return true;
	}


	/**
	 * @return string
	 * @throws ilCloudPluginConfigException
	 */
	public function getServerURL() {
		return $this->getValue(self::F_SERVER_URL);
	}


	/**
	 * @param bool $return_default if set and the variable is not set, the default value will be returned
	 *
	 * @return string
	 */
	public function getWebDAVPath($return_default = false) {
		$value = $this->getValue(self::F_WEBDAV_PATH);
		return (!$value && $return_default) ? self::DEFAULT_WEBDAV_PATH : $value;
	}


	/**
	 * @return string
	 */
	public function getServiceTitle() {
		return $this->getValue(self::F_TITLE);
	}


	/**
	 * @return string
	 */
	public function getServiceInfo() {
		return $this->getValue(self::F_DESCRIPTION);
	}


	/**
	 * @return bool
	 */
	public function getOAuth2Active() {
		return $this->getValue(self::F_OAUTH2_ACTIVE);
	}

	/**
	 * @return string
	 */
	public function getOAuth2ClientID() {
		return $this->getValue(self::F_OAUTH2_ACTIVE . '_' . self::F_OAUTH2_CLIENT_ID);
	}

	/**
	 * @return string
	 */
	public function getOAuth2ClientSecret() {
		return $this->getValue(self::F_OAUTH2_ACTIVE . '_' . self::F_OAUTH2_CLIENT_SECRET);
	}


	/**
	 * @param bool $return_default if set and the variable is not set, the default value will be returned
	 *
	 * @return string
	 */
	public function getOAuth2Path($return_default = false) {
		$value = $this->getValue(self::F_OAUTH2_ACTIVE . '_' . self::F_OAUTH2_PATH);
		return (!$value && $return_default) ? self::DEFAULT_OAUTH2_PATH : $value;
	}


	/**
	 * @return string
	 */
	public function getFullOAuth2Path() {
		static $path;
		if (!$path) {
			$path = rtrim($this->getServerURL(), '/') . '/' . rtrim(ltrim($this->getOAuth2Path(true), '/'), '/');
		}
		return $path;
	}


	/**
	 * @return string
	 */
	public function getFullWebDAVPath() {
		return rtrim($this->getServerURL(), '/') . '/' . rtrim(ltrim($this->getWebDAVPath(true), '/'), '/') . '/';
	}

	/**
	 * @param $key
	 *
	 * @return bool|string
	 * @throws ilCloudPluginConfigException
	 */
	public function getValue($key) {
		if (!isset(self::$value_cache[$key])) {
			self::$value_cache[$key] = parent::getValue($key);
		}

		return self::$value_cache[$key];
	}


	/**
	 * @param $key
	 * @param $value
	 *
	 * @throws ilCloudPluginConfigException
	 */
	public function setValue($key, $value) {
		unset(self::$value_cache[$key]);
		parent::setValue($key, $value);
	}
}
