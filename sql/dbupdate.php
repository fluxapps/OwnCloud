<#1>
<?php
require_once("./Customizing/global/plugins/Modules/Cloud/CloudHook/ownCloud/classes/class.ilOwnCloudPlugin.php");
$pl = ilOwnCloudPlugin::getInstance();

$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => 8,
        'notnull' => true
    ),
    'base_uri' => array(
        'type' => 'text',
        'length' => 256
    ),
    'username' => array(
        'type' => 'text',
        'length' => 256
    ),
    'password' => array(
        'type' => 'text',
        'length' => 256
    ),
    'proxy' => array(
        'type' => 'text',
        'length' => 256
    ),
);
global $ilDB;
$ilDB->createTable($pl->getPluginTableName(), $fields);
$ilDB->addPrimaryKey($pl->getPluginTableName(), array( "id" ));
?>
<#2>
<?php
require_once("./Customizing/global/plugins/Modules/Cloud/CloudHook/ownCloud/classes/class.ownclConfig.php");
$config = new ownclConfig();
$config->initDB();
?>
<#3>
<?php
require_once("./Customizing/global/plugins/Modules/Cloud/CloudHook/ownCloud/classes/class.ownclConfig.php");
$config = new ownclConfig();
$config->setValue(ownclConfig::F_TITLE, 'ownCloud');
$config->setValue(ownclConfig::F_DESCRIPTION, 'Anbindung des Cloud-Dienstes OwnCloud');
?>