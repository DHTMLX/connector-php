<?php
	require_once("../config.php");
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
	

   require("../../codebase/tree_connector.php");
   $tree = new TreeConnector($res, "PDO");
//   
   $tree->dynamic_loading(true);
   $tree->render_table("tasks","taskId","taskName","","parentId");
?>