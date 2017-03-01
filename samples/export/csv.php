<?php
	require_once("../config.php");
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
	
	

	require("../../codebase/grid_connector.php");
	require("../../codebase/convert.php");
	
	$convert = new ConvertService("http://dhtmlxgrid.appspot.com/export/csv");
	$convert->excel();
	
	$grid = new GridConnector($res, "PDO");
	$grid->set_limit(100);
	$config = new GridConfiguration();
	
	$grid = new GridConnector($res, "PDO");
	$grid->set_config($config);
	$grid->render_table("grid50000");
?>