<?php
	require_once("../config.php");
	require_once('../../codebase/db_pdo.php');
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
	

	require("../../codebase/scheduler_connector.php");

	ConnectorSecurity::$security_key = true;

	$_GET["id"] = 810;

	$grid = new JSONSchedulerConnector($res, "PDO");
	$grid->render_table("events","event_id","start_date, end_date, event_name");
?>