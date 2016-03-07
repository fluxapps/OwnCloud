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
	const F_DESCRIPTION = 'service_info';
	const F_TITLE = 'service_title';
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

		if (!$this->getBaseURL()) {
			throw new ilCloudException(- 1, 'Configuration of OwnCloud incomplete. Please contact your system administrator');
		}

		return true;
	}


	/**
	 * @return string
	 * @throws ilCloudPluginConfigException
	 */
	public function getBaseURL() {
		return $this->getValue(self::F_BASEURL);
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
