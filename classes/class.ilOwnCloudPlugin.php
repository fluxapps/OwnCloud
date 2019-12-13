<?php
require_once(__DIR__ . "/../vendor/autoload.php");

if (!class_exists('ilOwnCloudPlugin')) {
    /**
     * Class ilOwnCloudPlugin
     *
     * @author  Theodor Truffer <tt@studer-raimann.ch>
     */
    class ilOwnCloudPlugin extends ilCloudHookPlugin
    {

        const PLUGIN_NAME = 'OwnCloud';


        /**
         * @return string
         */
        public function getPluginName()
        {
            return self::PLUGIN_NAME;
        }


        /**
         * @var ilOwnCloudPlugin
         */
        private static $instance;


        /**
         * @return ilOwnCloudPlugin
         */
        public static function getInstance()
        {
            if (self::$instance === null) {
                self::$instance = new self();
            }

            return self::$instance;
        }


        /**
         * @return string
         */
        public function getAjaxLink()
        {
            return null;
        }


        /**
         * @return ownclApp
         */
        public function getOwnCloudApp($ilOwnCloud = null)
        {
            return ownclApp::getInstance($ilOwnCloud);
        }
    }
}