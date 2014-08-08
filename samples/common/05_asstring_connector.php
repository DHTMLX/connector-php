<?php
	require_once("../config.php");
	require_once('../../codebase/db_pdo.php');
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
	

	require("../../codebase/data_connector.php");
	$data = new JSONDataConnector($res, "PDO");
	$data->dynamic_loading(100);
	$data->asString(true);
	$json = $data->render_table("grid50000","item_id","item_nm,item_cd");
	echo "<strong>Generated json:</strong><br>";
	echo $json;
?>