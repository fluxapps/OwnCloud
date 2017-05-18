<?php
require_once(__DIR__ . '/Item/class.ownclItemFactory.php');
require_once(dirname(__DIR__) . '/class.ownclConfig.php');
require_once('DAVClient/DAVClient.php');
/**
 * Class ownclClient
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ownclClient {

	const AUTH_BEARER = 'auth_bearer';

	/**
	 * @var DAVClient
	 */
	protected $sabre_client;
	/**
	 * @var ownclApp
	 */
	protected $owncl_app;
	/**
	 * @var ilOwnCloud
	 */
	protected $pl;
	/**
	 * @var ownclConfig
	 */
	protected $config;
	const DEBUG = true;


	/**
	 * @param ownclApp $ownclApp
	 */
	public function __construct(ownclApp $ownclApp) {
		$this->setOwnCloudApp($ownclApp);
		$this->pl = ilOwnCloudPlugin::getInstance();
		$this->config = new ownclConfig();

	}


	/**
	 * @return ownclAuth
	 */
	protected function getAuth() {
		return $this->getOwnCloudApp()->getOwnclAuth();
	}


	/**
	 * @return DAVClient
	 */
	protected function getWebDAVClient() {
		if (!$this->sabre_client) {
			$this->sabre_client = new DAVClient($this->getAuth()->getClientSettings());
		}

		return $this->sabre_client;
	}

	public function hasConnection() {
		try {   //sabredav version 1.8 throws exception on missing connection
			$response = $this->getWebDAVClient()->request('GET', '', null, $this->getAuth()->getHeaders());
		} catch (Exception $e) {
			return false;
		}

		return ($response['statusCode'] < 400);
	}


	/**
	 * @param $id
	 *
	 * @return ownclFile[]|ownclFolder[]
	 */
	public function listFolder($id) {
		global $ilLog;
		$id = $this->urlencode(ltrim($id, '/'));
		$ilLog->write('listFolder: ' . $id);

		$settings = $this->getAuth()->getClientSettings();
		if ($client = $this->getWebDAVClient()) {
			$ilLog->write('listFolder: ' . $settings['baseUri'] . $id);

			$response = $client->propFind($settings['baseUri'] . $id, array(), 1, $this->getAuth()->getHeaders());
			$items = ownclItemFactory::getInstancesFromResponse($response);

			return $items;
		}

		return array();
	}


	/**
	 * @param $path
	 *
	 * @return bool
	 */
	public function folderExists($path) {
		return $this->itemExists($path);
	}


	/**
	 * @param $path
	 *
	 * @return bool
	 */
	public function fileExists($path) {
		return $this->itemExists($path);
	}


	/**
	 * @param $path
	 *
	 * @return ownclFile
	 * @throws ilCloudException
	 */
	public function deliverFile($path) {
		$str = ltrim($path, "/");
		$path = $this->urlencode($str);
		$response = $this->getWebDAVClient()->request('GET', $path, null, $this->getAuth()->getHeaders());
		if (self::DEBUG) {
			global $log;
			$log->write("[ownclClient]->deliverFile({$path}) | response status Code: {$response['statusCode']}");
		}
		$path = rawurldecode($path);
		$file_name = pathinfo($path, PATHINFO_FILENAME) . '.' . pathinfo($path, PATHINFO_EXTENSION);
		header("Content-type: " . $response['headers']['content-type']);
		//        header("Content-type: application/octet-stream");
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename=' . $file_name);
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . $response['headers']['content-length'][0]);
		echo $response['body'];
		exit;
	}


	/**
	 * @param $path
	 *
	 * @return bool
	 */
	public function createFolder($path) {
		$path = $this->urlencode($path);
		$response = $this->getWebDAVClient()->request('MKCOL', ltrim($path, '/'), null, $this->getAuth()->getHeaders());
		if (self::DEBUG) {
			global $log;
			$log->write("[ownclClient]->createFolder({$path}) | response status Code: {$response['statusCode']}");
		}

		return ($response['statusCode'] == 200);
	}


	/**
	 * urlencode without encoding slashes
	 *
	 * @param $str
	 *
	 * @return mixed
	 */
	protected function urlencode($str) {
		return str_replace('%2F', '/', rawurlencode($str));
	}


	/**
	 * @param $location
	 * @param $local_file_path
	 *
	 * @return bool
	 * @throws ilCloudException
	 */
	public function uploadFile($location, $local_file_path) {
		$location = $this->urlencode(ltrim($location, '/'));
		if ($this->fileExists($location)) {
			$basename = pathinfo($location, PATHINFO_FILENAME);
			$extension = pathinfo($location, PATHINFO_EXTENSION);
			$i = 1;
			while ($this->fileExists($basename . "({$i})." . $extension)) {
				$i ++;
			}
			$location = $basename . "({$i})." . $extension;
		}
		$response = $this->getWebDAVClient()->request('PUT', $location, file_get_contents($local_file_path), $this->getAuth()->getHeaders());
		if (self::DEBUG) {
			global $log;
			$log->write("[ownclClient]->uploadFile({$location}, {$local_file_path}) | response status Code: {$response['statusCode']}");
		}

		return ($response['statusCode'] == 200);
	}


	/**
	 * @param $path
	 *
	 * @return bool
	 */
	public function delete($path) {
		$response = $this->getWebDAVClient()->request('DELETE', ltrim($this->urlencode($path), '/'), null, $this->getAuth()->getHeaders());
		if (self::DEBUG) {
			global $log;
			$log->write("[ownclClient]->delete({$path}) | response status Code: {$response['statusCode']}");
		}

		return ($response['statusCode'] == 200);
	}


	/**
	 * @param $path
	 *
	 * @return bool
	 */
	protected function itemExists($path) {
		try {
			$request = $this->getWebDAVClient()->request('GET', $this->urlencode($path), null, $this->getAuth()->getHeaders());
		} catch (Exception $e) {
			return false;
		}

		return ($request['statusCode'] < 400);
	}


	/**
	 * @return ownclApp
	 */
	public function getOwnCloudApp() {
		return $this->owncl_app;
	}


	/**
	 * @param $owncl_app
	 */
	public function setOwnCloudApp($owncl_app) {
		$this->owncl_app = $owncl_app;
	}


	/**
	 * (re)initialize the client with settings from the owncloud object
	 */
	public function loadClient() {
		$this->sabre_client = new DAVClient($this->getAuth()->getClientSettings());
	}
}