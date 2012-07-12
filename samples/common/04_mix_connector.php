<?php
	require_once("../config.php");
	$res=mysql_connect($mysql_server,$mysql_user,$mysql_pass);
	mysql_select_db($mysql_db);

	require("../../codebase/data_connector.php");
	require("../../codebase/scheduler_connector.php");
	require("../../codebase/grid_connector.php");


	$details = new JSONDataConnector($res);
	$details->mix("active", "yes");
	$details->configure("types","typeid","name");

	$events = new JSONSchedulerConnector($res);
	$events->mix("types", $details, array(
		"typeid" => "type"
	));
	$events->render_table("tevents","event_id","start_date,end_date,event_name,type", "", "");
?>