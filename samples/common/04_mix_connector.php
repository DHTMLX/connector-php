<?php
	require_once("../config.php");
	require_once('../../codebase/db_pdo.php');
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
	

	require("../../codebase/data_connector.php");
	require("../../codebase/scheduler_connector.php");
	require("../../codebase/grid_connector.php");


	$details = new JSONDataConnector($res, "PDO");
	$details->mix("active", "yes");
	$details->configure("types","typeid","name");

	$events = new JSONSchedulerConnector($res, "PDO");
	$events->mix("types", $details, array(
		"typeid" => "type"
	));
	$events->render_table("tevents","event_id","start_date,end_date,event_name,type", "", "");
?>