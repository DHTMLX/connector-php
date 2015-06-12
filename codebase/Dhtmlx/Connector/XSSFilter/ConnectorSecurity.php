<?php
namespace Dhtmlx\Connector\XSSFilter;
use Dhtmlx\Connector\Tools\LogMaster;

define("DHX_SECURITY_SAFETEXT",  1);
define("DHX_SECURITY_SAFEHTML", 2);
define("DHX_SECURITY_TRUSTED", 3);

class ConnectorSecurity {
	static public $xss = DHX_SECURITY_SAFETEXT;
	static public $security_key = false;
	static public $security_var = "dhx_security";

	static private $filterClass = null;
	static function filter($value, $mode = false){
		if ($mode === false)
			$mode = ConnectorSecurity::$xss;

		if ($mode == DHX_SECURITY_TRUSTED)
			return $value;
		if ($mode == DHX_SECURITY_SAFETEXT)
			return filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
		if ($mode == DHX_SECURITY_SAFEHTML){
			if (ConnectorSecurity::$filterClass == null)
				ConnectorSecurity::$filterClass = new dhxExternalInputClean();
			return ConnectorSecurity::$filterClass->basic($value);
		}
		throw new Error("Invalid security mode:"+$mode);
	}

	static function CSRF_detected(){
		LogMaster::log("[SECURITY] Possible CSRF attack detected", array(
			"referer" => $_SERVER["HTTP_REFERER"],
			"remote" => $_SERVER["REMOTE_ADDR"]
		));
		LogMaster::log("Request data", $_POST);
		die();
	}
	static function checkCSRF($edit){
		if (ConnectorSecurity::$security_key){
			if (!isset($_SESSION))
				@session_start();

			if ($edit=== true){
				if (!isset($_POST[ConnectorSecurity::$security_var]))
					return ConnectorSecurity::CSRF_detected();
				$master_key = $_SESSION[ConnectorSecurity::$security_var];
				$update_key = $_POST[ConnectorSecurity::$security_var];
				if ($master_key != $update_key)
					return ConnectorSecurity::CSRF_detected();

				return "";
			}
			//data loading
			if (!array_key_exists(ConnectorSecurity::$security_var,$_SESSION)){
				$_SESSION[ConnectorSecurity::$security_var] = md5(uniqid());
			}

			return $_SESSION[ConnectorSecurity::$security_var];
		}

		return "";
	}

}