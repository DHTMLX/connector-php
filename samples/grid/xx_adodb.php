<?php

	require_once("../config.php");
	require_once("./adodb5/adodb.inc.php");

	$db = ADONewConnection('mysql');
	$db->Connect($mysql_server,$mysql_user,$mysql_pass, $mysql_db);

	require("../../codebase/grid_connector.php");
	require("../../codebase/db_adodb.php");
	$grid = new GridConnector($db, "Ado");
	$grid->dynamic_loading(100);
	$grid->render_table("grid50000","item_id","item_nm,item_cd");

?>