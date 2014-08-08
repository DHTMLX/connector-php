<?php
	require_once("../config.php");
	require_once('../../codebase/db_pdo.php');
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
	

	require("../../codebase/grid_connector.php");
	$grid = new GridConnector($res, "PDO");
	
	$grid->dynamic_loading(100);
	$grid->sort("item_nm ASC");
	$grid->sort("item_cd", "DESC");
	$grid->render_table("grid50000","item_id","item_nm,item_cd");
?>