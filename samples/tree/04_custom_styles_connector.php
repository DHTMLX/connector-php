<?php
	require_once("../config.php");
	require_once('../../codebase/db_pdo.php');
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
	
	
	require_once("../../codebase/tree_connector.php");
	$tree = new TreeConnector($res, "PDO");
//	
	function custom_format($item){
			if ($item->get_value("duration")>10)
				$item->set_image("lock.gif");
			if ($item->get_value("complete")>75) 
				$item->set_check_state(1);
	}
	$tree->event->attach("beforeRender",custom_format);
	$tree->render_sql("SELECT taskId,taskName,duration,complete from tasks WHERE complete>49","taskId","taskName","","parentId");
?>