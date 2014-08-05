<?php

	require_once("../config.php");
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
	
	
	require_once('../../codebase/treegridgroup_connector.php');
	$treegrid = new TreeGridGroupConnector($res, "PDO");
	
	$treegrid->render_table("products", "id", "product_name,scales,colour", "", "category");

?>