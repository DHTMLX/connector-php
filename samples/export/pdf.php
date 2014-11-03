<?php
	require_once("../config.php");
	require_once('../../codebase/db_pdo.php');
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
	
	

	require("../../codebase/grid_connector.php");
	require("../../codebase/convert.php");
	
	$convert = new ConvertService("http://dhtmlxgrid.appspot.com/export/pdf");
	
	$grid = new GridConnector($res, "PDO");
	$config = new GridConfiguration();
	
	$config->set_convert_mode(true);
	$grid->set_config($config);
	$grid->render_table("grid50");
?>