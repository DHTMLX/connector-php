<?php

	include ('../config.php');
	include ('../../codebase/gantt_connector.php');

    $res=mysql_connect($mysql_server,$mysql_user,$mysql_pass); 
    mysql_select_db($mysql_db); 
	
	$scheduler = new JSONGanttConnector($res);
	$scheduler->render_table("gantt_tasks","id","start_date,duration,text,progress,order,parent");
?>