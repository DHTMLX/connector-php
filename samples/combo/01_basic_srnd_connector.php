<?php
	require_once("../config.php");
	require_once("../../codebase/db_pdo.php");
	$res= new PDO($mysql_server,$mysql_user,$mysql_pass);
	

   require("../../codebase/combo_connector.php");
   $combo = new ComboConnector($res, "PDO");
//   $combo->enable_log("temp.log");
   $combo->dynamic_loading(2);
   $combo->render_table("country_data","country_id","name");
?>