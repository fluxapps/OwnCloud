<?php


/**
 * Class ilOwnCloudPluginCreationGUI
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilOwnCloudCreationGUI extends ilCloudPluginCreationGUI {

	public function initPluginCreationFormSection(ilRadioOption $option) {
		$config = new ownclConfig();
		$option->setTitle($config->getServiceTitle());
		$option->setInfo(nl2br($config->getServiceInfo()));
	}
}