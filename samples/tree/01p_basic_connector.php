<?php
	require_once("../config.php");
	require_once('../../codebase/db_pdo.php');
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
	

function child_setter($data){
	//the check is kind of lame, in real table you most probably may have some more stable way to detect is item have childs or not
	if ($data->get_value("taskId")%100>1) 
		$data->set_kids(false);
	else
		$data->set_kids(true);
}

   require("../../codebase/tree_connector.php");
   $tree = new TreeConnector($res, "PDO");
//   
   $tree->event->attach("beforeRender","child_setter");
   $tree->render_table("tasks","taskId","taskName","","parentId");
?>