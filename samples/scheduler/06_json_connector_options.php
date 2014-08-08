<?php
	include ('../../codebase/db_pdo.php');
	include ('../../codebase/scheduler_connector.php');
	include ('../config.php');
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
    

	$list = new JSONOptionsConnector($res, "PDO");
	$list->render_table("types","typeid","typeid(value),name(label)");

	$scheduler = new JSONSchedulerConnector($res, "PDO");

	$scheduler->set_options("type", $list);
	$scheduler->render_table("tevents","event_id","start_date,end_date,event_name,type");
?>