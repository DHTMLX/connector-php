<?php
	require_once("../config.php");
	require("../../codebase/db_pdo.php");
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);

	require("../../codebase/grid_connector.php");


$gridConn = new GridConnector($res, "PDO");
$sql = "SELECT * FROM grid50";
$gridConn->render_sql($sql,"item_id","item_nm,item_cd");

?>