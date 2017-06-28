<?php
require_once("./Modules/Cloud/classes/class.ilCloudPluginService.php");
require_once('./Modules/Cloud/exceptions/class.ilCloudException.php');
require_once("./Modules/Cloud/classes/class.ilCloudUtil.php");

/**
 * Class ilOwnCloudService
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilOwnCloudService extends ilCloudPluginService {

	public function __construct($service_name, $obj_id) {
		parent::__construct($service_name, $obj_id);
	}


	/**
	 * @return ownclApp
	 */
	public function getApp() {
		return $this->getPluginObject()->getOwnCloudApp();
	}

	/**
	 * @return ownclClient
	 */
	public function getClient() {
		return $this->getApp()->getOwnCloudClient();
	}


	/**
	 * @return ownclAuth
	 */
	public function getAuth() {
		return $this->getApp()->getOwnclAuth();
	}


	/**
	 * @param string $callback_url
	 */
	public function authService($callback_url = "") {
		$this->getAuth()->authenticate(htmlspecialchars_decode($callback_url));
	}


	/**
	 * @return bool
	 */
	public function afterAuthService() {
		global $ilCtrl;
		$ilCtrl->setCmd('edit');

		return $this->getAuth()->afterAuthentication($this->getPluginObject());
	}


	/**
	 * @param ilCloudFileTree $file_tree
	 * @param string          $parent_folder
	 *
	 * @throws Exception
	 */
	public function addToFileTree(ilCloudFileTree $file_tree, $parent_folder = "/") {
		try {
			$files = $this->getClient()->listFolder($parent_folder);
			foreach ($files as $k => $item) {
				$size = ($item instanceof ownclFile) ? $size = $item->getSize() : NULL;
				$is_dir = $item instanceof ownclFolder;
				$file_tree->addNode($item->getFullPath(), $k . $item->getId(), $is_dir, strtotime($item->getDateTimeLastModified()), $size);
			}
		} catch (Exception $e) {
			if ($this->getPluginObject()->getCloudModulObject()->getAuthComplete()) {
				global $ilCtrl;
				$this->getPluginObject()->getCloudModulObject()->setAuthComplete(false);
				$this->getPluginObject()->getCloudModulObject()->update();
				$ilCtrl->redirectByClass($_GET['cmdClass'], $_GET['cmd']);
			}
			throw $e;
		}
	}


	/**
	 * @param null            $path
	 * @param ilCloudFileTree $file_tree
	 */
	public function getFile($path = NULL, ilCloudFileTree $file_tree = NULL) {
		$this->getClient()->deliverFile($path);
	}


	/**
	 * @param                 $file
	 * @param                 $name
	 * @param string          $path
	 * @param ilCloudFileTree $file_tree
	 *
	 * @return mixed
	 */
	public function putFile($file, $name, $path = '', ilCloudFileTree $file_tree = NULL) {
		$path = ilCloudUtil::joinPaths($file_tree->getRootPath(), $path);
		if ($path == '/') {
			$path = '';
		}

		$return = $this->getClient()->uploadFile($path . "/" . $name, $file);

		return $return;
	}


	/**
	 * @param null            $path
	 * @param ilCloudFileTree $file_tree
	 *
	 * @return bool
	 */
	public function createFolder($path = NULL, ilCloudFileTree $file_tree = NULL) {
		if ($file_tree instanceof ilCloudFileTree) {
			$path = ilCloudUtil::joinPaths($file_tree->getRootPath(), $path);
		}

		if ($path != '/') {
			$this->getClient()->createFolder($path);
		}

		return true;
	}


	/**
	 * @param null            $path
	 * @param ilCloudFileTree $file_tree
	 *
	 * @return bool
	 */
	public function deleteItem($path = NULL, ilCloudFileTree $file_tree = NULL) {
		$path = ilCloudUtil::joinPaths($file_tree->getRootPath(), $path);

		return $this->getClient()->delete($path);
	}


	/**
	 * @return ilOwnCloud
	 */
	public function getPluginObject() {
		return parent::getPluginObject();
	}


	/**
	 * @return ilOwnCloudPlugin
	 */
	public function getPluginHookObject() {
		return parent::getPluginHookObject();
	}
}