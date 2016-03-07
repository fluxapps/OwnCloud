<?php
require_once(__DIR__ . '/class.ownclTree.php');
require_once(__DIR__ . '/class.ownclTreeGUI.php');

/**
 * Class ilOwnCloudSettingsGUI
 *
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 * @version           1.0.0
 *
 * @ilCtrl_IsCalledBy ilOwnCloudSettingsGUI : ilObjCloudGUI
 * @ingroup           ModulesCloud
 */
class ilOwnCloudSettingsGUI extends ilCloudPluginSettingsGUI {

	/**
	 * @var ilPropertyFormGUI
	 */
	protected $form;
	/**
	 * @var ilOwnCloudPlugin
	 */
	protected $pl;


	/**
	 * @param $service_name
	 */
	public function __construct($plugin_service_class) {
		parent::__construct($plugin_service_class);
		$this->pl = ilOwnCloudPlugin::getInstance();
	}


	public function initSettingsForm() {
		global $ilCtrl, $lng;

		$this->form = new ilPropertyFormGUI();

		// title
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setRequired(true);
		$this->form->addItem($ti);

		// description
		$ta = new ilTextAreaInputGUI($lng->txt("description"), "desc");
		$this->form->addItem($ta);

		// online
		$cb = new ilCheckboxInputGUI($lng->txt("online"), "online");
		$this->form->addItem($cb);

		$folder = new ilTextInputGUI($lng->txt("cld_root_folder"), "root_folder");
		if (!$this->cloud_object->currentUserIsOwner()) {
			$folder->setDisabled(true);
			$folder->setInfo($lng->txt("cld_only_owner_has_permission_to_change_root_path"));
		}

		$folder->setMaxLength(255);
		$folder->setSize(50);
		$this->form->addItem($folder);

		$this->createPluginSection();
		$this->initPluginSettings();

		$this->form->addCommandButton("updateSettings", $lng->txt("save"));

		$this->form->setTitle($lng->txt("cld_edit_Settings"));
		$this->form->setFormAction($ilCtrl->getFormActionByClass("ilCloudPluginSettingsGUI"));
	}


	public function setRootFolder() {
		global $ilCtrl, $lng;
		$root_path = $_GET['root_path'];
		$this->getPluginObject()->getCloudModulObject()->setRootFolder($root_path);
		$this->getPluginObject()->getCloudModulObject()->update();
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirectByClass('ilCloudPluginSettingsGUI', 'editSettings');
	}


	protected function initPluginSettings() {
		$item = new ilTextInputGUI($this->pl->txt('username'), 'username');
		$item->setRequired(true);
		$this->form->addItem($item);
		$title = $this->getPluginObject()->getAdminConfigObject()->getServiceTitle();
		$item = new ilPasswordInputGUI($this->pl->txt('password'), 'password');
		$item->setInfo(sprintf($this->pl->txt('password_info'), $title));
		$item->setRetype(false);
		$item->setRequired(true);
		$this->form->addItem($item);
	}


	protected function getPluginSettingsValues(&$values) {
		$values['username'] = $this->getPluginObject()->getUsername();
		$values['password'] = $this->getPluginObject()->getPassword();
	}


	public function updatePluginSettings() {
		$this->setTabs("general");

		$this->getPluginObject()->setUsername($this->form->getInput("username"));
		$this->getPluginObject()->setPassword($this->form->getInput("password"));
		$this->getPluginObject()->doUpdate();

		$client = $this->getPluginObject()->getOwnCloudApp()->getOwnCloudClient();
		if (!$client->hasConnection()) {
			$title = $this->getPluginObject()->getAdminConfigObject()->getServiceTitle();
			ilUtil::sendFailure(sprintf($this->pl->txt('no_connection'), $title), true);
		}
	}


	/**
	 * Edit Settings. This commands uses the form class to display an input form.
	 */
	function editSettings() {
		global $tpl, $ilTabs, $lng, $ilCtrl;

		if ($_GET['active_subtab'] == 'choose_root') {
			$this->setTabs("choose_root");
			$this->showTreeView();
		}

		$this->setTabs("general");

		try {
			$this->initSettingsForm();
			$this->getSettingsValues();
			$client = $this->getPluginObject()->getOwnCloudApp()->getOwnCloudClient();
			if (!$client->hasConnection()) {
				$title = $this->getPluginObject()->getAdminConfigObject()->getServiceTitle();
				ilUtil::sendFailure(sprintf($this->pl->txt('no_connection'), $title), true);
			}
			$tpl->setContent($this->form->getHTML());
		} catch (Exception $e) {
			ilUtil::sendFailure($e->getMessage());
		}
	}


	/**
	 * @return ilOwnCloud
	 */
	public function getPluginObject() {
		return parent::getPluginObject();
	}


	/**
	 * @param $active
	 */
	protected function setTabs($active) {
		global $ilTabs, $ilCtrl, $lng;
		$ilTabs->activateTab("settings");

		$ilCtrl->setParameter($this, 'active_subtab', 'general');
		$ilTabs->addSubTab("general", $lng->txt("general_settings"), $ilCtrl->getLinkTarget($this, 'editSettings'));
		$ilCtrl->setParameter($this, 'active_subtab', 'choose_root');
		$ilTabs->addSubTab("choose_root", $this->getPluginObject()->getPluginHookObject()
			->txt("subtab_choose_root"), $ilCtrl->getLinkTarget($this, 'editSettings'));
		$ilTabs->activateSubTab($active);
	}


	public function showTreeView() {
		global $tpl, $ilCtrl;
		$client = $this->getPluginObject()->getOwnCloudApp()->getOwnCloudClient();
		if ($client->hasConnection()) {
			$tree = new ownclTree($client);
			$tree_gui = new ownclTreeGUI('tree_expl', 'ilCloudPluginSettingsGUI', 'setRootFolder', $tree);
			ilUtil::sendInfo($this->getPluginObject()->getPluginHookObject()->txt('choose_root'), true);
			$tpl->setContent($tree_gui->getHTML());
			$tpl->show();
			exit;
		} else {
			$ilCtrl->setParameter($this, 'active_subtab', 'general');
			$ilCtrl->redirect($this, 'editSettings');
		}
	}
}

?>
