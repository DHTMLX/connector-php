<?php
	require_once("../config.php");
	require_once('../../codebase/db_pdo.php');
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
	

	require("../../codebase/dataview_connector.php");

	ConnectorSecurity::$security_key = true;

	$grid = new DataViewConnector($res, "PDO");
	$grid->set_limit(10);
	$grid->render_table("grid50000","item_id","item_nm,item_cd");
?>