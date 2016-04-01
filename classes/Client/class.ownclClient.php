<?php
require_once(__DIR__ . '/Item/class.ownclItemFactory.php');
require_once(dirname(__DIR__) . '/class.ownclConfig.php');
use Sabre\DAV\Client;

/**
 * Class ownclClient
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ownclClient {

	/**
	 * @var Sabre\DAV\Client
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
	const DEBUG = true;


	/**
	 * @param ownclApp $ownclApp
	 */
	public function __construct(ownclApp $ownclApp) {
		$this->setOwnCloudApp($ownclApp);
		$this->pl = ilOwnCloudPlugin::getInstance();
		if (PHP_VERSION_ID < 50400) {   //sabredav 3.0 is not supported for php version < 5.4
			require_once(dirname(dirname(__DIR__)) . '/lib/SabreDAV-1.8.12/vendor/autoload.php');
		} else {
			require_once(dirname(dirname(__DIR__)) . '/lib/SabreDAV-3.0.0/vendor/autoload.php');
		}
	}


	protected function getSabreClient() {
		if (!$this->sabre_client) {
			$settings = $this->getObjectSettings();
			$this->sabre_client = new Client($settings);
		}

		return $this->sabre_client;
	}


	public function hasConnection() {
		try {   //sabredav version 1.8 throws exception on missing connection
			$response = $this->getSabreClient()->request('GET');
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
		$id = $this->urlencode(ltrim($id, '/'));
		$settings = $this->getObjectSettings();
		if ($client = $this->getSabreClient()) {
			$response = $client->propFind($settings['baseUri'] . $id, array(), 1);
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
		$path = $this->urlencode($path);
		$response = $this->getSabreClient()->request('GET', $path);
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
		$response = $this->getSabreClient()->request('MKCOL', ltrim($path, '/'));
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
		$response = $this->getSabreClient()->request('PUT', $location, file_get_contents($local_file_path));
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
		$response = $this->getSabreClient()->request('DELETE', ltrim($this->urlencode($path), '/'));
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
			$request = $this->getSabreClient()->request('GET', $this->urlencode($path));
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
		$this->exod_app = $owncl_app;
	}


	/**
	 * @return array
	 */
	protected function getObjectSettings() {
		$obj_id = ilObject2::_lookupObjectId((int)$_GET['ref_id']);
		$obj = new ilOwnCloud('OwnCloud', $obj_id);
		$conf = new ownclConfig();
		$settings = array(
			'baseUri' => rtrim($conf->getBaseURL(), '/') . '/',
			'userName' => $obj->getUsername(),
			'password' => $obj->getPassword(),
		);

		return $settings;
	}
}