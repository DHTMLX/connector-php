<?php
	require_once("../config.php");
	require("../../codebase/db_pdo.php");
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
	
	
	function color_rows($row){
		if ($row->get_index()%2) {
			$row->set_row_style("background-color: red");
		}
	}
	require("../../codebase/grid_connector.php");
	$grid = new GridConnector($res, "PDO");
	
	$grid->dynamic_loading(100);
	$grid->event->attach("beforeRender","color_rows");
	$grid->render_table("grid50000","item_id","item_nm,item_cd");
?>