<?php

/**
 * Class ilOwnCloudSettingsGUI
 *
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 * @version           1.0.0
 *
 * @ilCtrl_IsCalledBy ilOwnCloudSettingsGUI : ilObjCloudGUI
 * @ingroup           ModulesCloud
 */
class ilOwnCloudSettingsGUI extends ilCloudPluginSettingsGUI
{

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
    public function __construct($plugin_service_class)
    {
        parent::__construct($plugin_service_class);
        $this->pl = ilOwnCloudPlugin::getInstance();
    }


    public function initSettingsForm()
    {
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

        // root folder
        $folder = new ilTextInputGUI($this->pl->txt("root_folder"), "root_folder");
        if (!$this->cloud_object->currentUserIsOwner()) {
            $folder->setDisabled(true);
            $folder->setInfo($lng->txt("cld_only_owner_has_permission_to_change_root_path"));
        } else {
            $ilCtrl->setParameter($this, 'action', "choose_root");
            $folder->setInfo("<a href='{$ilCtrl->getLinkTarget($this, 'editSettings')}'>" . $this->pl->txt("link_choose_root") . "</a>");
        }

        $folder->setMaxLength(255);
        $folder->setSize(50);
        $this->form->addItem($folder);

        if ($this->getAdminConfigObject()->getValue(ownclConfig::F_COLLABORATION_APP_INTEGRATION)) {
            $open_in_owncloud = new ilCheckboxInputGUI($this->txt('allow_open_in_owncloud'), 'allow_open_in_owncloud');
            $this->form->addItem($open_in_owncloud);
        }

        $this->getPluginObject()->getOwnCloudApp()->getOwnclAuth()->initPluginSettings($this->form);

        $this->form->addCommandButton("updateSettings", $lng->txt("save"));

        $this->form->setTitle($lng->txt("cld_edit_Settings"));
        $this->form->setFormAction($ilCtrl->getFormActionByClass("ilCloudPluginSettingsGUI"));
    }


    /**
     *
     */
    public function setRootFolder()
    {
        global $ilCtrl, $lng;
        $root_path = $_GET['root_path'];
        $this->getPluginObject()->getCloudModulObject()->setRootFolder($root_path);
        $this->getPluginObject()->getCloudModulObject()->update();
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirectByClass('ilCloudPluginSettingsGUI', 'editSettings');
    }


    /**
     *
     */
    protected function initPluginSettings()
    {
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


    /**
     * @param $values
     */
    protected function getPluginSettingsValues(&$values)
    {
        $values['username'] = $this->getPluginObject()->getUsername();
        $values['password'] = $this->getPluginObject()->getPassword();
        if ($this->getAdminConfigObject()->getValue(ownclConfig::F_COLLABORATION_APP_INTEGRATION)) {
            $values['allow_open_in_owncloud'] = $this->getPluginObject()->isAllowOpenInOwncloud();
        }
    }


    /**
     * @return ilOwnCloud
     */
    public function getPluginObject()
    {
        return parent::getPluginObject();
    }


    /**
     * @throws ilCloudException
     */
    public function updatePluginSettings()
    {
        global $ilCtrl;

        $this->setTabs();

        $client = $this->getPluginObject()->getOwnCloudApp()->getOwnCloudClient();
        $had_connection = $client->hasConnection();

        $this->getPluginObject()->setUsername($this->form->getInput("username"));
        $this->getPluginObject()->setPassword($this->form->getInput("password"));
        if ($this->getAdminConfigObject()->getValue(ownclConfig::F_COLLABORATION_APP_INTEGRATION)) {
            $this->getPluginObject()->setAllowOpenInOwncloud($this->form->getInput("allow_open_in_owncloud"));
        }
        $this->getPluginObject()->doUpdate();

        $client->loadClient();
        $has_connection = $client->hasConnection();
        // show tree view if client found connection after the update
        if (!$had_connection && $has_connection) {
            $ilCtrl->setParameter($this, 'action', 'choose_root');
        } else {
            $ilCtrl->setParameter($this, 'action', 'general');
        }

        if (!$client->hasConnection()) {
            ilUtil::sendFailure($this->getPluginObject()->getPluginHookObject()->txt('no_connection'), true);
        }
    }


    /**
     * Edit Settings. This commands uses the form class to display an input form.
     */
    function editSettings()
    {
        global $tpl, $ilCtrl;

        if ($root_path = $_GET['root_path']) {
            $this->setRootFolder($root_path);
        }

        if ($_GET['action'] == 'choose_root') {
            $ilCtrl->setParameter($this, "action", "choose_root");
            $this->showTreeView();
        }

        $this->setTabs();

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
     * @param $active
     */
    protected function setTabs($back = false)
    {
        /** @var $ilTabs ilTabsGUI */
        global $ilTabs, $lng, $ilCtrl;

        if (!$back) {
            $ilTabs->activateTab("settings");
        } else {
            $ilTabs->clearTargets();
            $ilCtrl->setParameter($this, "action", '');
            $ilTabs->setBackTarget($lng->txt('back'), $ilCtrl->getLinkTarget($this, 'editSettings'));
            $ilCtrl->setParameter($this, "action", 'choose_root');
        }
    }


    public function showTreeView()
    {
        global $tpl, $ilCtrl;
        $this->setTabs(true);
        $client = $this->getPluginObject()->getOwnCloudApp()->getOwnCloudClient();
        if ($client->hasConnection()) {
            $tree = new ownclTree($client);
            $tree_gui = new ownclTreeGUI('tree_expl', $this, 'editSettings', $tree);
            if ($tree_gui->handleCommand()) {
                return;
            }
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
