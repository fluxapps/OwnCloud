<?php
require_once('./Modules/Cloud/classes/class.ilCloudPlugin.php');

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
	 * @var string
	 */
	protected $access_token = '';
	/**
	 * @var string
	 */
	protected $refresh_token = '';
	/**
	 * @var string
	 */
	protected $valid_through = '';
	/**
	 * @var int
	 */
	protected $validation_user_id = 6;


	/**
	 * @param      $service_name
	 * @param      $obj_id
	 * @param null $cloud_modul_object
	 *
	 * @throws ilCloudException
	 */
	public function __construct($service_name, $obj_id, $cloud_modul_object = NULL) {
		parent::__construct('OwnCloud', $obj_id, $cloud_modul_object);
	}


	/**
	 * @param $token League\OAuth2\Client\Token\AccessToken
	 */
	public function storeToken($token) {
		global $ilUser;
		$this->setAccessToken($token->getToken());
		$this->setRefreshToken($token->getRefreshToken());
		$this->setValidThrough($token->getExpires());
		$this->setValidationUserId($ilUser->getId());
		$this->doUpdate();
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
			'access_token' => array(
				'text',
				$this->getAccessToken()
			),
			'refresh_token' => array(
				'text',
				$this->getRefreshToken()
			),
			'valid_through' => array(
				'integer',
				$this->getValidThrough()
			),
			'validation_user_id' => array(
				'integer',
				$this->getValidationUserId()
			),
		);
	}


	/**
	 *
	 */
	public function flushTokens() {
		$this->setValidThrough(0);
		$this->setAccessToken('');
		$this->setRefreshToken('');
		$this->doUpdate();
	}


	/**
	 * @return ownclApp
	 * @throws ilCloudException
	 */
	public function getOwnCloudApp() {
		$app = ilOwnCloudPlugin::getInstance()->getOwnCloudApp($this);

		global $ilUser;
		if ($ilUser->getId() == $this->getOwnerId()) {
			if (!$app->getOwnclAuth()->checkAndRefreshAuthentication() && $this->getCloudModulObject()->getAuthComplete()) {
				$this->getCloudModulObject()->setAuthComplete(false);
				$this->getCloudModulObject()->doUpdate();
				$this->flushTokens();
			}
		} else {
			throw new ilCloudException(ilCloudException::AUTHENTICATION_FAILED, 'Der Ordner kann zur Zeit nur vom Besitzer geöffnet werden.');
		}

		return $app;
	}

	public function isTokenExpired() {
		return ((int)$this->getValidThrough() != 0) && ($this->getValidThrough() <= time());
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


	/**
	 * @return string
	 */
	public function getAccessToken() {
		return $this->access_token;
	}


	/**
	 * @param string $access_token
	 */
	public function setAccessToken($access_token) {
		$this->access_token = $access_token;
	}


	/**
	 * @return string
	 */
	public function getRefreshToken() {
		return $this->refresh_token;
	}


	/**
	 * @param string $refresh_token
	 */
	public function setRefreshToken($refresh_token) {
		$this->refresh_token = $refresh_token;
	}


	/**
	 * @return string
	 */
	public function getValidThrough() {
		return $this->valid_through;
	}


	/**
	 * @param string $valid_through
	 */
	public function setValidThrough($valid_through) {
		$this->valid_through = $valid_through;
	}


	/**
	 * @return int
	 */
	public function getValidationUserId() {
		return $this->validation_user_id;
	}


	/**
	 * @param int $validation_user_id
	 */
	public function setValidationUserId($validation_user_id) {
		$this->validation_user_id = $validation_user_id;
	}


}