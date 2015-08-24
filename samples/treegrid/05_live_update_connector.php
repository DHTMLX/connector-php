<?php
	require_once("../config.php");
	require("../../codebase/db_pdo.php");
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
	

    require("../../codebase/treegrid_connector.php"); 

    $treegrid = new GridConnector($res, "PDO"); 
    $treegrid->enable_log("temp.log",true); 
    $treegrid->enable_live_update("actions_table"); 
    $treegrid->render_table("tasks","taskId","taskName,duration,complete","","parentId"); 
?>