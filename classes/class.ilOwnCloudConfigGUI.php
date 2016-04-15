<?php
require_once(__DIR__ . '/App/class.ownclApp.php');
require_once(__DIR__ . '/class.ownclConfig.php');
require_once('./Modules/Cloud/classes/class.ilCloudPluginConfigGUI.php');

/**
 * Class ilOwnCloudConfigGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilOwnCloudConfigGUI extends ilCloudPluginConfigGUI {

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
	public function getFields() {
		return array(
			ownclConfig::F_TITLE => array('type' => self::IL_TEXT_INPUT_GUI, 'required' => true, 'subelements' => NULL),
			ownclConfig::F_DESCRIPTION => array('type' => self::IL_TEXTAREA_INPUT_GUI, 'required' => false, 'subelements' => NULL),
			ownclConfig::F_BASEURL => array( 'type' => self::IL_TEXT_INPUT_GUI, 'required' => true, 'subelements' => NULL ),
		);
	}


	public function initConfigurationForm() {
		global $lng, $ilCtrl;

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		foreach ($this->fields as $key => $item) {
			$field = new $item["type"]($this->plugin_object->txt('cfg_' . $key), $key);
			if ($item["type"] == self::IL_SELECT_INPUT_GUI) {
				$field->setOptions($item['options']);
			}
			if (isset($item["info"])) {
				$field->setInfo($this->plugin_object->txt($item["info"]));
			}
			if (is_array($item["subelements"])) {
				foreach ($item["subelements"] as $subkey => $subitem) {
					$subfield = new $subitem["type"]($this->plugin_object->txt('cfg_' . $key . "_" . $subkey), $key . "_" . $subkey);
					$subfield->setInfo($this->plugin_object->txt($subitem["info"]));
					$field->addSubItem($subfield);
				}
			}
			$field->setRequired((bool)$item['required']);
			$this->form->addItem($field);
		}

		$this->form->addCommandButton("save", $lng->txt("save"));

		$this->form->setTitle($this->plugin_object->txt("configuration"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));

		return $this->form;
	}
}