<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Class ownclAuthOAuth2
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ownclAuthOAuth2 implements ownclAuth {
	const CALLBACK_URL = 'owncl_callback_url';
	const AUTH_BEARER = 'owncl_access_token';
	/**
	 * @var ownclApp
	 */
	protected $app;
	/**
	 * @var OAuth2Provider
	 */
	protected $oauth2_provider;
	/**
	 * @var ownclConfig
	 */
	protected $config;
	/**
	 * @var ownclOAuth2UserToken
	 */
	protected $user_token;


	/**
	 * ownclAuthOAuth2 constructor.
	 *
	 * @param ownclApp $app
	 */
	public function __construct(ownclApp $app) {
		$this->setApp($app);
		$this->config = new ownclConfig();
		$this->oauth2_provider = new OAuth2Provider(array(
			'clientId' => $this->config->getOAuth2ClientID(),
			'clientSecret' => $this->config->getOAuth2ClientSecret(),
			'redirect_uri' => $this->getRedirectUri(),
			'urlAuthorize' => $this->config->getFullOAuth2Path() . '/authorize',
			'urlAccessToken' => $this->config->getFullOAuth2Path() . '/api/v1/token',
			'urlResourceOwnerDetails' => $this->config->getFullOAuth2Path() . '/resource',
		));
	}

	public function getHeaders() {
		return array('Authorization' => 'Bearer ' . $this->getToken()->getAccessToken());
	}

	/**
	 * @return array
	 */
	public function getClientSettings() {
		$settings = array(
			'baseUri' => $this->config->getFullWebDAVPath(),
		);

		return $settings;
	}


	public function checkAndRefreshAuthentication() {
		if (!$this->getToken()->getAccessToken() && !$this->getToken()->getRefreshToken()) {
		    ownclLog::getInstance()->write('No access or refresh token found for user with id ' . $this->getToken()->getUserId());
			return false;
		}
		if ($this->getToken()->isExpired()) {
			try {
				$this->refreshToken();
				ownclLog::getInstance()->write('Token successfully refreshed for user with id ' . $this->getToken()->getUserId());
				return true;
			} catch (Exception $e) {
			    ownclLog::getInstance()->write('Exception: Token refresh for user with id ' . $this->getToken()->getUserId() . ' failed with message: ' . $e->getMessage());
				return false;
			}
		}
		return true;
	}


	/**
	 *
	 */
	public function refreshToken() {
			$this->getToken()->storeUserToken($this->oauth2_provider->getAccessToken('refresh_token', array(
				'refresh_token' => $this->getToken()->getRefreshToken()
			)));
	}


	/**
	 * @param String $callback_url
	 *
	 * @return bool
	 * @throws ilCloudException
	 */
	public function authenticate($callback_url) {
		global $ilUser;
		if ($this->getToken()->getAccessToken() && $this->getApp()->getOwnCloudClient()->hasConnection()) {
			header("Location: " . htmlspecialchars_decode($callback_url));
			return true;
		}
		if ($ilUser->getId() != $this->getApp()->getIlOwnCloud()->getOwnerId()) {
			throw new ilCloudException(ilCloudException::AUTHENTICATION_FAILED, 'Der Ordner kann zur Zeit nur vom Besitzer geöffnet werden.');
		}
		ilSession::set(self::CALLBACK_URL, $this->getApp()->getHttpPath() . $callback_url);
		$this->oauth2_provider->authorize(array('redirect_uri' => $this->getRedirectUri()));
	}

	/**
	 * @return string
	 */
	protected function getRedirectUri() {
		return $this->getApp()->getHttpPath() . 'Customizing/global/plugins/Modules/Cloud/CloudHook/OwnCloud/redirect.php';
	}

	/**
	 *
	 */
	public function redirectToObject() {
		$this->storeTokenToSession($this->oauth2_provider->getAccessToken('authorization_code', array(
			'code' => $_GET['code'],
			'redirect_uri' => $this->getRedirectUri())));

		ilUtil::redirect(ilSession::get(self::CALLBACK_URL));
	}


	/**
	 * @param $access_token League\OAuth2\Client\Token\AccessToken
	 */
	protected function storeTokenToSession($access_token) {
		ilSession::set(self::AUTH_BEARER, serialize($access_token));
	}


	/**
	 * @return League\OAuth2\Client\Token\AccessToken
	 */
	protected function loadTokenFromSession() {
		return unserialize(ilSession::get(self::AUTH_BEARER));
	}



	/**
	 * @param ilOwnCloud $object
	 *
	 * @return bool
	 */
	public function afterAuthentication($object) {
		if (!$this->getApp()->getOwnCloudClient()->hasConnection()) {
			$token = unserialize(ilSession::get(self::AUTH_BEARER));
			$this->getToken()->storeUserToken($token);
		}

		// since the auth token are per user and not per object,
		// all objects of this user have to be marked as authenticated
		foreach ($object->getAllWithSameOwner() as $obj_id) {
			$ilObjCloud = new ilObjCloud($obj_id, false);
			$ilObjCloud->setAuthComplete(true);
			$ilObjCloud->update();
		}

		return true;
	}


	public function initPluginSettings(&$form) {
		$n = new ilNonEditableValueGUI(ilOwnCloudPlugin::getInstance()->txt('info_token_expires'));
		$n->setValue(date('d.m.Y - H:i:s', $this->getToken()->getValidThrough()));
		$form->addItem($n);
	}


	/**
	 * @return ownclOAuth2UserToken
	 */
	public function getToken() {
		if (!$this->user_token) {
			global $ilUser;
			$ilOwnCloud = $this->getApp()->getIlOwnCloud();
			// at object creation, the object and owner id does not yet exist, therefore we take the current user's id
			$this->user_token = ownclOAuth2UserToken::getUserToken($ilOwnCloud ? $ilOwnCloud->getOwnerId() : $ilUser->getId());
		}
		return $this->user_token;
	}


	/**
	 * @return ownclApp
	 */
	public function getApp() {
		return $this->app;
	}


	/**
	 * @param ownclApp $app
	 */
	public function setApp($app) {
		$this->app = $app;
	}
}