<?php

	require_once("../config.php");
	$res=mysql_connect($mysql_server,$mysql_user,$mysql_pass);
	mysql_select_db($mysql_db);

	require("../../codebase/data_connector.php");
	require("../../codebase/scheduler_connector.php");
	require("../../codebase/mixed_connector.php");

	$data1 = new JSONDataConnector($res);
	$data1->configure("country_data", "country_id", "name,full_name,type");

	$data2 = new JSONTreeDataConnector($res);
	$data2->configure("tasks","taskId","taskName","","parentId");

	$conn = new MixedConnector($res);
	$conn->add("country_data", $data1);
	$conn->add("countries", $data2);
	$conn->render();

?>