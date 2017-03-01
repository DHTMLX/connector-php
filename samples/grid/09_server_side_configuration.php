<?php
	require_once("../config.php");
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
	

	require("../../codebase/grid_connector.php");
	$grid = new GridConnector($res, "PDO");
	$grid->set_limit(100);
	
	$config = new GridConfiguration();
	$config->setHeader("ID,First Name,Last Name,Title,Office,Extn,Mobile,Email");
	$config->setColTypes("ro,ed,ed,ed,ed,ed,ed,ed");
	$grid->set_config($config);
   
	$grid->render_table("grid50000");
?>