<?php
chdir('../../../../../../../');
require_once('./Services/Init/classes/class.ilInitialisation.php');
ilInitialisation::initILIAS();

require_once('./Customizing/global/plugins/Modules/Cloud/CloudHook/OwnCloud/classes/class.ilOwnCloudPlugin.php');

ilOwnCloudPlugin::getInstance()->getOwnCloudApp()->getOwnclAuth()->redirectToObject();
exit;
?>