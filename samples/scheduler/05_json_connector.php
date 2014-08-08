<?php

	include ('../config.php');
	include ('../../codebase/db_pdo.php');
	include ('../../codebase/scheduler_connector.php');

    $res= new PDO($mysql_server,$mysql_user,$mysql_pass); 
     

	$scheduler = new JSONSchedulerConnector($res, "PDO");
	$scheduler->render_table("events","event_id","start_date,end_date,event_name,details");
?>