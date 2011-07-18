<?php
/*
	@author dhtmlx.com
	@license GPL, see license.txt
*/
require_once("base_connector.php");

class CommonDataProcessor extends DataProcessor{
	protected function get_post_values($ids){
		if (isset($_GET['action'])){
			$data = array();
			if (isset($_POST["id"]))
				$data[$_POST["id"]] = $_POST;
			else
				$data["dummy_id"] = $_POST;
			return $data;
		}
		return parent::get_post_values($ids);
	}
	
	protected function get_ids(){
		if (isset($_GET['action'])){
			if (isset($_POST["id"]))
				return array($_POST['id']);
			else
				return array("dummy_id");
		}
		return parent::get_ids();
	}
	
	protected function get_operation($rid){
		if (isset($_GET['action']))
			return $_GET['action'];
		return parent::get_operation($rid);
	}
	
	public function output_as_xml($results){
		if (isset($_GET['action'])){
			LogMaster::log("Edit operation finished",$results);
			ob_clean();
			$type = $results[0]->get_status();
			if ($type == "error" || $type == "invalid"){
				echo "false";
			} else if ($type=="insert"){
				echo "true\n".$results[0]->get_new_id();
			} else 
				echo "true";
		} else
			return parent::output_as_xml($results);
	}
};

/*! DataItem class for DataView component
**/
class CommonDataItem extends DataItem{
	/*! return self as XML string
	*/
	function to_xml(){
		if ($this->skip) return "";
		
		$str="<item id='".$this->get_id()."' >";
		for ($i=0; $i<sizeof($this->config->text); $i++){
			$extra = $this->config->text[$i]["name"];
			$str.="<".$extra."><![CDATA[".$this->data[$extra]."]]></".$extra.">";
		}
		return $str."</item>";
	}
}


/*! Connector class for DataView
**/
class DataConnector extends Connector{
	
	/*! constructor
		
		Here initilization of all Masters occurs, execution timer initialized
		@param res 
			db connection resource
		@param type
			string , which hold type of database ( MySQL or Postgre ), optional, instead of short DB name, full name of DataWrapper-based class can be provided
		@param item_type
			name of class, which will be used for item rendering, optional, DataItem will be used by default
		@param data_type
			name of class which will be used for dataprocessor calls handling, optional, DataProcessor class will be used by default. 
	*/	
	public function __construct($res,$type=false,$item_type=false,$data_type=false){
		if (!$item_type) $item_type="CommonDataItem";
		if (!$data_type) $data_type="CommonDataProcessor";
		parent::__construct($res,$type,$item_type,$data_type);
	}

	protected function parse_request_mode(){
		//do nothing, at least for now
	}
	
	//parse GET scoope, all operations with incoming request must be done here
	protected function parse_request(){
		if (isset($_GET['action'])){
			$action = $_GET['action'];
			//simple request mode
			if ($action == "get"){
				//data request
				if (isset($_GET['id'])){
					//single entity data request
					$this->request->set_filter($this->config->id["name"],$_GET['id'],"=");
				} else {
					//loading collection of items
				}
			} else {
				//data saving
				$this->editing = true;
			}
		} else {
			if (isset($_GET['editing']) && isset($_POST['ids']))
				$this->editing = true;			
			
			parent::parse_request();
		}
	}
	
	/*! renders self as  xml, starting part
	*/
	protected function xml_start(){
		return "<data>";
	}	
};

class JSONDataConnector extends DataConnector{
	public function __construct($res,$type=false,$item_type=false,$data_type=false){
		if (!$item_type) $item_type="JSONCommonDataItem";
		if (!$data_type) $data_type="CommonDataProcessor";
		$this->data_separator = ",\n";
		parent::__construct($res,$type,$item_type,$data_type);
	}
	
	protected function output_as_xml($res){
		$start = "[\n";
		$end = substr($this->render_set($res),0,-2)."\n]";
		
		$out = new OutputWriter($start, $end);
		$out->set_type("json");
		$this->event->trigger("beforeOutput", $this, $out);
		$out->output("", true, $this->encoding);
	}
}

class JSONCommonDataItem extends DataItem{
	/*! return self as XML string
	*/
	function to_xml(){
		if ($this->skip) return "";
		
		$data = array( "id" => $this->get_id() );
		for ($i=0; $i<sizeof($this->config->text); $i++){
			$extra = $this->config->text[$i]["name"];
			$data[$extra]=$this->data[$extra];
		}
		return json_encode($data);
	}
}

?>