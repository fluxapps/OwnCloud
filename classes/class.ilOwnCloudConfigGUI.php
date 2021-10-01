<?php
require_once(__DIR__ . "/../vendor/autoload.php");

/**
 * Class ilOwnCloudConfigGUI
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilOwnCloudConfigGUI extends ilCloudPluginConfigGUI
{

    const IL_CHECKBOX_INPUT_GUI = 'ilCheckboxInputGUI';
    const IL_TEXT_INPUT_GUI = 'ilTextInputGUI';
    const IL_NUMBER_INPUT_GUI = 'ilNumberInputGUI';
    const IL_SELECT_INPUT_GUI = 'ilSelectInputGUI';
    const IL_TEXTAREA_INPUT_GUI = 'ilTextAreaInputGUI';
    /**
     * @var ilPropertyFormGUI
     */
    protected $form;

    /**
     * @return array
     */
    public function getFields()
    {
        global $DIC;

        return array(
            ownclConfig::F_TITLE => array('type' => self::IL_TEXT_INPUT_GUI, 'required' => true, 'subelements' => null),
            ownclConfig::F_DESCRIPTION => array('type' => self::IL_TEXTAREA_INPUT_GUI,
                                                'required' => false,
                                                'subelements' => null
            ),
            ownclConfig::F_SERVER_URL => array('type' => self::IL_TEXT_INPUT_GUI,
                                               'required' => true,
                                               'subelements' => null
            ),
            ownclConfig::F_BASE_DIRECTORY => array(
                'type' => self::IL_TEXT_INPUT_GUI,
                'required' => false,
                'subelements' => null,
                "info" => $this->plugin_object->txt('cfg_' . ownclConfig::F_BASE_DIRECTORY . '_info')
            ),
            ownclConfig::F_WEBDAV_PATH => array(
                'type' => self::IL_TEXT_INPUT_GUI,
                'required' => false,
                'subelements' => null,
                "info" => sprintf($this->plugin_object->txt("cfg_default_info"), ownclConfig::DEFAULT_WEBDAV_PATH)
            ),
            ownclConfig::F_OAUTH2_ACTIVE => array(
                'type' => self::IL_CHECKBOX_INPUT_GUI,
                'required' => false,
                'subelements' => array(
                    ownclConfig::F_OAUTH2_CLIENT_ID => array('type' => self::IL_TEXT_INPUT_GUI,
                                                             'required' => true,
                                                             'subelements' => null
                    ),
                    ownclConfig::F_OAUTH2_CLIENT_SECRET => array('type' => self::IL_TEXT_INPUT_GUI,
                                                                 'required' => true,
                                                                 'subelements' => null
                    ),
                    ownclConfig::F_OAUTH2_PATH => array(
                        'type' => self::IL_TEXT_INPUT_GUI,
                        'required' => false,
                        'subelements' => null,
                        "info" => sprintf($this->plugin_object->txt("cfg_default_info"),
                            ownclConfig::DEFAULT_OAUTH2_PATH)
                    ),
                    ownclConfig::F_OAUTH2_TOKEN_REQUEST_AUTH => [
                        'type' => self::IL_SELECT_INPUT_GUI,
                        'required' => true,
                        'subelements' => null,
                        'options' => [
                            ownclConfig::HEADER => $this->plugin_object->txt(ownclConfig::HEADER),
                            ownclConfig::POST_BODY => $this->plugin_object->txt(ownclConfig::POST_BODY),
                        ]
                    ]
                )
            ),
            ownclConfig::F_COLLABORATION_APP_INTEGRATION => array(
                'type' => self::IL_CHECKBOX_INPUT_GUI,
                'required' => false,
                'info' => $this->plugin_object->txt("cfg_" . ownclConfig::F_COLLABORATION_APP_INTEGRATION . '_info'),
                'subelements' => array(
                    ownclConfig::F_COLLABORATION_APP_URL => array(
                        'type' => self::IL_TEXT_INPUT_GUI,
                        'required' => true,
                        'info' => $this->plugin_object->txt("cfg_" . ownclConfig::F_COLLABORATION_APP_INTEGRATION . '_' . ownclConfig::F_COLLABORATION_APP_URL . '_info')
                    ),
                    ownclConfig::F_COLLABORATION_APP_FORMATS => array(
                        'type' => self::IL_TEXTAREA_INPUT_GUI,
                        'required' => true,
                        'info' => $this->plugin_object->txt("cfg_" . ownclConfig::F_COLLABORATION_APP_INTEGRATION . '_' . ownclConfig::F_COLLABORATION_APP_FORMATS . '_info')
                    ),
                    ownclConfig::F_USER_MAPPING_FIELD => array(
                        'type' => self::IL_SELECT_INPUT_GUI,
                        'required' => true,
                        'info' => $this->plugin_object->txt("cfg_" . ownclConfig::F_COLLABORATION_APP_INTEGRATION . '_' . ownclConfig::F_USER_MAPPING_FIELD . '_info'),
                        'options' => array(
                            'login' => $DIC->language()->txt('login'),
                            'ext_account' => $DIC->language()->txt('user_ext_account'),
                            'email' => $DIC->language()->txt('email'),
                            'second_email' => $DIC->language()->txt('second_email')
                        )
                    )
                )
            )
        );
    }

    public function initConfigurationForm()
    {
        global $lng, $ilCtrl;

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();

        foreach ($this->fields as $key => $item) {
            $field = new $item["type"]($this->plugin_object->txt('cfg_' . $key), $key);
            if ($item["type"] == self::IL_SELECT_INPUT_GUI) {
                $field->setOptions($item['options']);
            }
            if (isset($item["info"])) {
                $field->setInfo($item["info"]);
            }
            if (is_array($item["subelements"])) {
                foreach ($item["subelements"] as $subkey => $subitem) {
                    $subfield = new $subitem["type"]($this->plugin_object->txt('cfg_' . $key . "_" . $subkey),
                        $key . "_" . $subkey);
                    if ($subitem["type"] == self::IL_SELECT_INPUT_GUI) {
                        $subfield->setOptions($subitem['options']);
                    }
                    if (isset($subitem["info"])) {
                        $subfield->setInfo($subitem["info"]);
                    }
                    $subfield->setRequired((bool) $subitem['required']);
                    $field->addSubItem($subfield);
                }
            }
            $field->setRequired((bool) $item['required']);
            $this->form->addItem($field);
        }

        $this->form->addCommandButton("save", $lng->txt("save"));

        $this->form->setTitle($this->plugin_object->txt("configuration"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));

        return $this->form;
    }
}
