<?php
	require_once("../../codebase/db_pdo.php");
	require_once("../../codebase/dataview_connector.php");
	require_once("../config.php");
	
	$conn =  new PDO($mysql_server,$mysql_user,$mysql_pass);
	
	
	$data = new DataViewConnector($conn);
	$data->render_sql(" SELECT * FROM packages_plain WHERE Id < 1000","Id","Package,Version,Maintainer");
?>