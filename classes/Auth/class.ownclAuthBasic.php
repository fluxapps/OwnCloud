<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'class.ownclAuth.php';

/**
 * Class ownclAuthBasic
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ownclAuthBasic implements ownclAuth {

	/**
	 * @var ownclApp
	 */
	protected $app;
	/**
	 * @var ownclConfig
	 */
	protected $config;

	/**
	 * ownclAuthBasic constructor.
	 *
	 * @param ownclApp $app
	 */
	public function __construct(ownclApp $app) {
		$this->setApp($app);
		$this->config = new ownclConfig();
	}

	public function getHeaders() {
		return array();
	}

	/**
	 * @return array
	 */
	public function getClientSettings() {
		$obj_id = ilObject2::_lookupObjectId((int)$_GET['ref_id']);
		$obj = new ilOwnCloud('OwnCloud', $obj_id);

		$settings = array(
			'baseUri' => $this->config->getFullWebDAVPath(),
			'userName' => $obj->getUsername(),
			'password' => $obj->getPassword(),
		);

		return $settings;
	}


	public function authenticate($callback_url) {
		header("Location: " . htmlspecialchars_decode($callback_url));
	}


	public function afterAuthentication($object) {
		return true;
	}


	public function initPluginSettings(&$form) {
		global $lng;
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->getApp()->getIlOwnCloud()->getCloudModulObject()->getServiceName()." ".$lng->txt("cld_service_specific_settings"));
		$form->addItem($section);

		$item = new ilTextInputGUI(ilOwnCloudPlugin::getInstance()->txt('username'), 'username');
		$item->setRequired(true);
		$form->addItem($item);
		$title = $this->getApp()->getIlOwnCloud()->getAdminConfigObject()->getServiceTitle();
		$item = new ilPasswordInputGUI(ilOwnCloudPlugin::getInstance()->txt('password'), 'password');
		$item->setInfo(sprintf(ilOwnCloudPlugin::getInstance()->txt('password_info'), $title));
		$item->setRetype(false);
		$item->setRequired(true);
		$form->addItem($item);

		return true;
	}


	public function checkAndRefreshAuthentication() {
		return true;
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


	/**
	 * @return ownclConfig
	 */
	public function getConfig() {
		return $this->config;
	}


	/**
	 * @param ownclConfig $config
	 */
	public function setConfig($config) {
		$this->config = $config;
	}


}