<?php
	require_once("../config.php");
	require_once('../../codebase/db_pdo.php');
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
	

	require("../../codebase/tree_connector.php");

	ConnectorSecurity::$security_key = true;

	$grid = new TreeConnector($res, "PDO");
	$grid->render_table("tasks","taskId","taskName","","parentId");
?>