<?php
require_once('./Modules/Cloud/classes/class.ilCloudPlugin.php');
require_once('Auth/Token/class.ownclOAuth2UserToken.php');

/**
 * Class ilOwnCloud
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilOwnCloud extends ilCloudPlugin {

	/**
	 * @var String
	 */
	protected $base_uri;
	/**
	 * @var String
	 */
	protected $username;
	/**
	 * @var String
	 */
	protected $password;
	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @param      $service_name
	 * @param      $obj_id
	 * @param null $cloud_modul_object
	 *
	 * @throws ilCloudException
	 */
	public function __construct($service_name, $obj_id, $cloud_modul_object = NULL) {
		global $ilUser;
		$this->user = $ilUser;
		parent::__construct('OwnCloud', $obj_id, $cloud_modul_object);
	}


	/**
	 * @return bool
	 */
	public function read() {
		global $ilDB;

		$set = $ilDB->query('SELECT * FROM ' . $this->getTableName() . ' WHERE id = ' . $ilDB->quote($this->getObjId(), 'integer'));
		$rec = $ilDB->fetchObject($set);
		if ($rec == NULL) {
			return false;
		} else {
			foreach ($this->getArrayForDb() as $k => $v) {
				$this->{$k} = $rec->{$k};
			}
		}
		$this->setMaxFileSize(500);

		return true;
	}


	public function doUpdate() {
		global $ilDB;
		$ilDB->update($this->getTableName(), $this->getArrayForDb(), array( 'id' => array( 'integer', $this->getObjId() ) ));
	}


	public function doDelete() {
		global $ilDB;

		$ilDB->manipulate('DELETE FROM ' . $this->getTableName() . ' WHERE ' . ' id = ' . $ilDB->quote($this->getObjId(), 'integer'));
	}


	public function create() {
		global $ilDB;

		$ilDB->insert($this->getTableName(), $this->getArrayForDb());
	}


	/**
	 * @return array
	 */
	protected function getArrayForDb() {
		return array(
			'id' => array(
				'text',
				$this->getObjId()
			),
			'base_uri' => array(
				'text',
				$this->getBaseUri()
			),
			'username' => array(
				'text',
				$this->getUsername()
			),
			'password' => array(
				'text',
				$this->getPassword()
			),
		);
	}


	/**
	 * @return ownclApp
	 * @throws ilCloudException
	 */
	public function getOwnCloudApp() {
		$app = ilOwnCloudPlugin::getInstance()->getOwnCloudApp($this);

		$config = new ownclConfig();
		if (!$config->getOAuth2Active()) {
			return $app;
		}

		$status = $app->getOwnCloudClient()->getHTTPStatus();
		if ($status == 401 && $this->getCloudModulObject()->getAuthComplete()) {
			$this->getCloudModulObject()->setAuthComplete(false);
			$this->getCloudModulObject()->doUpdate();
			if ($this->user->getId() != $this->getCloudModulObject()->getOwnerId()) {
				throw new ilCloudException(ilCloudException::AUTHENTICATION_FAILED, 'Der Ordner kann zur Zeit nur vom Besitzer geöffnet werden.');
			} else {
				throw new ilCloudException(ilCloudException::AUTHENTICATION_FAILED, $this->getPluginHookObject()->txt('not_authorized'));
			}
		} else if ($status > 401 || $status == false) {
			throw new ilCloudException(ilCloudException::AUTHENTICATION_FAILED, $this->getPluginHookObject()->txt('no_connection'));
		}

		return $app;
	}



	/**
	 * @param String $password
	 */
	public function setPassword($password) {
		$this->password = $password;
	}


	/**
	 * @return String
	 */
	public function getPassword() {
		return $this->password;
	}


	/**
	 * @param String $username
	 */
	public function setUsername($username) {
		$this->username = $username;
	}


	/**
	 * @return String
	 */
	public function getUsername() {
		return $this->username;
	}


	/**
	 * @param String $base_uri
	 */
	public function setBaseUri($base_uri) {
		$this->base_uri = $base_uri;
	}


	/**
	 * @return String
	 */
	public function getBaseUri() {
		return $this->base_uri;
	}



}