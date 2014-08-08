<?php
	require_once("../../codebase/db_pdo.php");
	require_once("../../codebase/dataview_connector.php");
	require_once("../config.php");
	
	$conn =  new PDO($mysql_server,$mysql_user,$mysql_pass);
	
	
	$data = new DataViewConnector($conn);
	$data->render_table("packages_small","Id","Package,Version,Maintainer");
?>