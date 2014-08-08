<?php

	require_once("../config.php");
	require_once('../../codebase/db_pdo.php');
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
	

	require("../../codebase/data_connector.php");
	require("../../codebase/scheduler_connector.php");
	require("../../codebase/mixed_connector.php");

	$data1 = new JSONDataConnector($res, "PDO");
	$data1->configure("country_data", "country_id", "name,full_name,type");

	$data2 = new JSONTreeDataConnector($res, "PDO");
	$data2->configure("tasks","taskId","taskName","","parentId");

	$conn = new MixedConnector($res, "PDO");
	$conn->add("country_data", $data1);
	$conn->add("countries", $data2);
	$conn->render();

?>