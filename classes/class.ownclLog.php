<?php

/**
 * Class ownclLog
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ownclLog extends ilLog
{

    const OC_LOG = 'oc.log';
    /**
     * @var ownclLog
     */
    protected static $instance;


    /**
     * @return ownclLog
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            if (ILIAS_LOG_DIR === "php:/" && ILIAS_LOG_FILE === "stdout") {
                // Fix Docker-ILIAS log
                self::$instance = new self(ILIAS_LOG_DIR, ILIAS_LOG_FILE);
            } else {
                self::$instance = new self(ILIAS_LOG_DIR, self::OC_LOG);
            }
        }

        return self::$instance;
    }


    function write($a_msg, $a_log_level = null)
    {
        parent::write($a_msg, $a_log_level);
    }


    /**
     * @return mixed
     */
    public function getLogDir()
    {
        return ILIAS_LOG_DIR;
    }


    /**
     * @return string
     */
    public function getLogFile()
    {
        if (ILIAS_LOG_DIR === "php:/" && ILIAS_LOG_FILE === "stdout") {
            // Fix Docker-ILIAS log
            return ILIAS_LOG_FILE;
        } else {
            return self::OC_LOG;
        }
    }


    /**
     * @return string
     */
    public static function getFullPath()
    {
        $log = self::getInstance();

        return $log->getLogDir() . '/' . $log->getLogFile();
    }
}

?>
