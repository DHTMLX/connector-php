<?php

	include ('../config.php');
	require_once("../../codebase/db_pdo.php");
	include ('../../codebase/gantt_connector.php');

    $res= new PDO($mysql_server,$mysql_user,$mysql_pass); 
     

	$gantt = new JSONGanttConnector($res, "PDO");

	$gantt->mix("open", 1);

	$gantt->render_links("gantt_links", "id", "source,target,type");
	$gantt->render_table("gantt_tasks","id","start_date,duration,text,progress,parent","");

?>