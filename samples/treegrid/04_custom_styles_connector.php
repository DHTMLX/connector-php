<?php
	require_once("../config.php");
	require_once('../../codebase/db_pdo.php');
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
	
	
	require_once("../../codebase/treegrid_connector.php");
	$tree = new TreeGridConnector($res, "PDO");
	
	function custom_format($item){
			$item->set_row_color($item->get_value("complete")<75?"#AAFFFF":"#FFAAFF");
			if ($item->get_value("duration")>10)
				$item->set_image("true.gif");
			else
				$item->set_image("false.gif");
	}
	$tree->event->attach("beforeRender",custom_format);
	$tree->render_sql("SELECT * from tasks WHERE complete>49","taskId","taskName,duration,complete","","parentId");
?>