<?php
namespace Dhtmlx\Connector\Tools;

/*! Controls error and debug logging.
	Class designed to be used as static object.
**/
class LogMaster {
    private static $_log=false;//!< logging mode flag
    private static $_output=false;//!< output error infor to client flag
    private static $session="";//!< all messages generated for current request

    /*! convert array to string representation ( it is a bit more readable than var_dump )

        @param data
            data object
        @param pref
            prefix string, used for formating, optional
        @return
            string with array description
    */
    private static function log_details($data,$pref=""){
        if (is_array($data)){
            $str=array("");
            foreach($data as $k=>$v)
                array_push($str,$pref.$k." => ".LogMaster::log_details($v,$pref."\t"));
            return implode("\n",$str);
        }
        return $data;
    }
    /*! put record in log

        @param str
            string with log info, optional
        @param data
            data object, which will be added to log, optional
    */
    public static function log($str="",$data=""){
        if (LogMaster::$_log){
            $message = $str.LogMaster::log_details($data)."\n\n";
            LogMaster::$session.=$message;
            error_log($message,3,LogMaster::$_log);
        }
    }

    /*! get logs for current request
        @return
            string, which contains all log messages generated for current request
    */
    public static function get_session_log(){
        return LogMaster::$session;
    }

    /*! error handler, put normal php errors in log file

        @param errn
            error number
        @param errstr
            error description
        @param file
            error file
        @param line
            error line
        @param context
            error cntext
    */
    public static function error_log($errn,$errstr,$file,$line,$context){
        LogMaster::log($errstr." at ".$file." line ".$line);
    }

    /*! exception handler, used as default reaction on any error - show execution log and stop processing

        @param exception
            instance of Exception
    */
    public static function exception_log($exception){
        LogMaster::log("!!!Uncaught Exception\nCode: " . $exception->getCode() . "\nMessage: " . $exception->getMessage());
        if (LogMaster::$_output){
            echo "<pre><xmp>\n";
            echo LogMaster::get_session_log();
            echo "\n</xmp></pre>";
        }
        die();
    }

    /*! enable logging

        @param name
            path to the log file, if boolean false provided as value - logging will be disabled
        @param output
            flag of client side output, if enabled - session log will be sent to client side in case of an error.
    */
    public static function enable_log($name,$output=false){
        LogMaster::$_log=$name;
        LogMaster::$_output=$output;
        if ($name){
            set_error_handler(array("Dhtmlx\\Connector\\Tools\\LogMaster","error_log"),E_ALL);
            set_exception_handler(array("Dhtmlx\\Connector\\Tools\\LogMaster","exception_log"));
            LogMaster::log("\n\n====================================\nLog started, ".date("d/m/Y h:i:s")."\n====================================");
        }
    }
}