<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Class ownclAuth
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface ownclAuth {

	/**
	 * @return bool
	 */
	public function checkAndRefreshAuthentication();

	/**
	 * @param $callback_url String
	 */
	public function authenticate($callback_url);


	/**
	 * @param $object ilOwnCloud
	 *
	 * @return bool
	 */
	public function afterAuthentication($object);

	/**
	 * @return array
	 */
	public function getClientSettings();


	/**
	 * @return array|null
	 */
	public function getHeaders();


	/**
	 * @param $form ilPropertyFormGUI
	 *
	 * @return mixed
	 */
	public function initPluginSettings(&$form);

}