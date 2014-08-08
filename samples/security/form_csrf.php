<?php
	require_once("../config.php");
	require_once('../../codebase/db_pdo.php');
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
	

	require("../../codebase/form_connector.php");

	ConnectorSecurity::$security_key = true;

	$_GET["id"] = 810;

	$grid = new FormConnector($res, "PDO");
	$grid->render_table("grid50000","item_id","item_nm,item_cd");
?>