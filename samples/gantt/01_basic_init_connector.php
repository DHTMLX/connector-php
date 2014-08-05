<?php

	include ('../config.php');
	include ('../../codebase/gantt_connector.php');

    $res= new PDO($mysql_server,$mysql_user,$mysql_pass); 
     
	
	$gantt = new JSONGanttConnector($res, "PDO");
    $gantt->openAll();
    $gantt->render_links("gantt_links", "id", "source_task,target_task,type");
	$gantt->render_table("gantt_tasks","id","start_date,duration,text,progress,order,parent");
?>