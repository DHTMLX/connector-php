<?php
	require_once("../config.php");
	require("../../codebase/db_pdo.php");
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
	
	require("../../codebase/grid_connector.php");
	$grid = new GridConnector($res, "PDO");
	
	$grid->dynamic_loading(100);
	$grid->render_table("countries","item_id","item_nm,item_cd");
?>