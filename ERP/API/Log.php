<?php
/**
 * Class Log
 * Writing Error Logs
 * Created By : Bipin
 * Date : 10-December-2019
 */

$api_error_log_file = '';

define('API_ERROR_LOG_FILE', 'api_error.log');

class Log {

    /**
     * @param $message
     * Write log - ERROR
     */
    public static function error($message) {

        $message = "ERROR - ".$message;
        self::write($message);

    }

    /**
     * @param $message
     * Write log - WARNING
     */
    public static function warning($message) {

        $message = "WARNING - ".$message;
        self::write($message);

    }

    /**
     * @param $message
     * Write log - WARNING
     */
    public static function notice($message) {

        $message = "NOTICE - ".$message;
        self::write($message);

    }

    /**
     * @param $message
     * Write to log file
     */
    public static function write($message) {
        if(!empty($message))
            file_put_contents(API_ERROR_LOG_FILE, $message."\n", FILE_APPEND);
    }


}

