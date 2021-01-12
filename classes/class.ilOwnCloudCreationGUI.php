<?php

/**
 * Class ilOwnCloudPluginCreationGUI
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilOwnCloudCreationGUI extends ilCloudPluginCreationGUI
{

    const F_BASE_DIRECTORY = 'base_directory';

    public function initPluginCreationFormSection(ilRadioOption $option)
    {
        $config = new ownclConfig();
        $option->setTitle($config->getServiceTitle());
        $option->setInfo(nl2br($config->getServiceInfo()));

        $base_directory = new ilTextInputGUI($this->txt('creation_frm_' . self::F_BASE_DIRECTORY), self::F_BASE_DIRECTORY);
        $base_directory->setInfo($this->txt('creation_frm_' . self::F_BASE_DIRECTORY . '_info'));
        $base_directory->setValue($this->getAdminConfigObject()->getValue(ownclConfig::F_BASE_DIRECTORY));
        $option->addSubItem($base_directory);
    }

    /**
     * @param ilObjCloud        $obj
     * @param ilPropertyFormGUI $form
     * @throws ilCloudException
     * @throws ilCloudPluginConfigException
     */
    public function afterSavePluginCreation(ilObjCloud &$obj, ilPropertyFormGUI $form)
    {
        $base_dir = $form->getInput(self::F_BASE_DIRECTORY);
        if ($base_dir) {
            $root_folder = '/' . ltrim($base_dir, '/');
            $obj->setRootFolder($root_folder);
        }
        parent::afterSavePluginCreation($obj, $form);
    }

}
