<?php

/**
 * Class ilOwnCloudActionListGUI
 *
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy ilOwnCloudActionListGUI : ilObjCloudGUI
 */
class ilOwnCloudActionListGUI extends ilCloudPluginActionListGUI
{

    const CMD_OPEN_IN_COLLABORATION_APP = 'openInCollaborationApp';
    const ITEM_ID = 'item_id';
    const ITEM_PATH = 'item_path';
    /**
     * @var bool
     */
    protected $open_in_owncloud_active;
    /**
     * @var array
     */
    protected static $only_office_formats
        = array(
            'doc',
            'docx',
            'dot',
            'dotx',
            'odt',
            'ott',
            'rtf',
            'txt',
            'pdf',
            'pdfa',
            'html',
            'epub',
            'xps',
            'djvu',
            'djv'
        );


    /**
     * @return bool
     * @throws ilCloudException
     */
    protected function addItemsAfter()
    {
        global $DIC;
        $format = strtolower(pathinfo($this->node->getPath(), PATHINFO_EXTENSION));
        if (!$this->node->getIsDir()
            && in_array($format, self::$only_office_formats)
            && $this->isOpenInOwnCloudActive()
        ) {
            $DIC->ctrl()->setParameterByClass(ilCloudPluginActionListGUI::class, self::ITEM_ID, $this->node->getId());
            $DIC->ctrl()->setParameterByClass(ilCloudPluginActionListGUI::class, self::ITEM_PATH, urlencode($this->node->getPath()));
            $this->selection_list->addItem(
                ilOwnCloudPlugin::getInstance()->txt('open_in_owncloud'),
                '',
                $DIC->ctrl()->getLinkTargetByClass([ilObjCloudGUI::class, ilCloudPluginActionListGUI::class], self::CMD_OPEN_IN_COLLABORATION_APP),
                '',
                '',
                '_blank'
            );
        }

        return true;
    }


    /**
     * @return bool
     * @throws ilCloudPluginConfigException
     */
    protected function isOpenInOwnCloudActive()
    {
        if (is_null($this->open_in_owncloud_active)) {
            $this->open_in_owncloud_active = $this->getAdminConfigObject()->getValue(ownclConfig::F_COLLABORATION_APP_INTEGRATION)
                && $this->getPluginObject()->isAllowOpenInOwncloud();
        }

        return $this->open_in_owncloud_active;
    }


    /**
     *
     */
    protected function openInCollaborationApp()
    {
        global $DIC;
        $path = filter_input(INPUT_GET, self::ITEM_PATH, FILTER_SANITIZE_STRING);
        $id = filter_input(INPUT_GET, self::ITEM_ID, FILTER_SANITIZE_STRING);

        /** @var ownclClient $client */
        $client = $this->getService()->getClient();
        $client->shareItem($path, $DIC->user());

        $url = (new ownclConfig())->getFullCollaborationAppPath($id, urlencode($path));
        Header('Location: ' . $url);
        exit;
    }


    /**
     * @return ownclConfig
     */
    public function getAdminConfigObject()
    {
        return parent::getAdminConfigObject();
    }


    /**
     * @return ilOwnCloud
     */
    public function getPluginObject()
    {
        return parent::getPluginObject();
    }
}