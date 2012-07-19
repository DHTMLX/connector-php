<?php
	require_once("../config.php");
	$res=mysql_connect($mysql_server,$mysql_user,$mysql_pass);
	mysql_select_db($mysql_db);

	require("../../codebase/data_connector.php");
	$data = new JSONDataConnector($res);
	$data->dynamic_loading(100);
	$data->asString(true);
	$json = $data->render_table("grid50000","item_id","item_nm,item_cd");
	echo "<strong>Generated json:</strong><br>";
	echo $json;
?>