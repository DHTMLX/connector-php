<?php
	require_once("../config.php");
	require_once('../../codebase/db_pdo.php');
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
	

   require("../../codebase/treegrid_connector.php");
   $tree = new TreeGridConnector($res, "PDO");
   
   $tree->render_sql("SELECT * from tasks WHERE complete>49","taskId","taskName,duration,complete","","parentId");
?>