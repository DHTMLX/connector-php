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
		return $this->to_xml_start().$this->to_xml_end();
	}

	function to_xml_start(){
		$str="<item id='".$this->get_id()."' ";
		for ($i=0; $i < sizeof($this->config->text); $i++){ 
			$name=$this->config->text[$i]["name"];
			$str.=" ".$name."='".$this->xmlentities($this->data[$name])."'";
		}
		return $str.">";
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
	
		if (isset($_GET["start"]) && isset($_GET["count"]))
			$this->request->set_limit($_GET["start"],$_GET["count"]);
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
		
		if ($this->dload){
			$start = "{ \"data\":".$start.$end;
			if ($pos=$this->request->get_start())
				$end = ", \"pos\":".$pos." }";
			else
				$end = ", \"pos\":0, \"total_count\":".$this->sql->get_size($this->request)." }";
		}
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

class TreeCommonDataItem extends CommonDataItem{
	protected $kids=-1;

	function to_xml_start(){
		$str="<item id='".$this->get_id()."' ";
		for ($i=0; $i < sizeof($this->config->text); $i++){ 
			$name=$this->config->text[$i]["name"];
			$str.=" ".$name."='".$this->xmlentities($this->data[$name])."'";
		}
		
		if ($this->kids === true)
			$str .=" dhx_kids='1'";
		
		return $str.">";
	}

	function has_kids(){
		return $this->kids;
	}

	function set_kids($value){
		$this->kids=$value;
	}
}


class TreeDataConnector extends DataConnector{
	protected $id_swap = array();
	public function __construct($res,$type=false,$item_type=false,$data_type=false){
		if (!$item_type) $item_type="TreeCommonDataItem";
		if (!$data_type) $data_type="CommonDataProcessor";
		parent::__construct($res,$type,$item_type,$data_type);

		$this->event->attach("afterInsert",array($this,"parent_id_correction_a"));
		$this->event->attach("beforeProcessing",array($this,"parent_id_correction_b"));
	}

	protected function xml_start(){
		return "<data parent='".$this->request->get_relation()."'>";
	}	

	/*! store info about ID changes during insert operation
		@param dataAction 
			data action object during insert operation
	*/
	public function parent_id_correction_a($dataAction){
		$this->id_swap[$dataAction->get_id()]=$dataAction->get_new_id();
	}
	/*! update ID if it was affected by previous operation
		@param dataAction 
			data action object, before any processing operation
	*/
	public function parent_id_correction_b($dataAction){
		$relation = $this->config->relation_id["db_name"];
		$value = $dataAction->get_value($relation);
		
		if (array_key_exists($value,$this->id_swap))
			$dataAction->set_value($relation,$this->id_swap[$value]);
	}

	//parse GET scoope, all operations with incoming request must be done here
	protected function parse_request(){
		parent::parse_request();

		if (isset($_GET["parent"]))
			$this->request->set_relation($_GET["parent"]);
		else
			$this->request->set_relation("0");
	}

	protected function render_set($res){
		$output="";
		$index=0;
		while ($data=$this->sql->get_next($res)){
			$data = new $this->names["item_class"]($data,$this->config,$index);
			$this->event->trigger("beforeRender",$data);
		//there is no info about child elements, 
		//if we are using dyn. loading - assume that it has,
		//in normal mode just exec sub-render routine			
			if ($data->has_kids()===-1 && $this->dload)
					$data->set_kids(true);
			$output.=$data->to_xml_start();
			if ($data->has_kids()===-1 || ( $data->has_kids()==true && !$this->dload)){
				$sub_request = new DataRequestConfig($this->request);
				$sub_request->set_relation($data->get_id());
				$output.=$this->render_set($this->sql->select($sub_request));
			}
			$output.=$data->to_xml_end();
			$index++;
		}
		return $output;
	}

}


class JSONTreeDataConnector extends TreeDataConnector{

	public function __construct($res,$type=false,$item_type=false,$data_type=false){
		if (!$item_type) $item_type="JSONTreeCommonDataItem";
		if (!$data_type) $data_type="CommonDataProcessor";
		parent::__construct($res,$type,$item_type,$data_type);

		$this->event->attach("afterInsert",array($this,"parent_id_correction_a"));
		$this->event->attach("beforeProcessing",array($this,"parent_id_correction_b"));
	}

	protected function render_set($res){
		$output=array();
		$index=0;
		while ($data=$this->sql->get_next($res)){
			$data = new $this->names["item_class"]($data,$this->config,$index);
			$this->event->trigger("beforeRender",$data);
		//there is no info about child elements, 
		//if we are using dyn. loading - assume that it has,
		//in normal mode just exec sub-render routine			
			if ($data->has_kids()===-1 && $this->dload)
					$data->set_kids(true);
			$record = &$data->to_xml_start();
			if ($data->has_kids()===-1 || ( $data->has_kids()==true && !$this->dload)){
				$sub_request = new DataRequestConfig($this->request);
				$sub_request->set_relation($data->get_id());
				$temp = &$this->render_set($this->sql->select($sub_request));
				if (sizeof($temp))
					$record["data"] = $temp;
			}
			$output[] = $record;
			$index++;
		}
		return $output;
	}	

	protected function output_as_xml($res){
		$data = array();
		$data["parent"] = $this->request->get_relation();
		$data["data"] = $this->render_set($res);

		$out = new OutputWriter(json_encode($data), "");
		$out->set_type("json");
		$this->event->trigger("beforeOutput", $this, $out);
		$out->output("", true, $this->encoding);
	}

}


class JSONTreeCommonDataItem extends TreeCommonDataItem{
	/*! return self as XML string
	*/
	function to_xml_start(){
		if ($this->skip) return "";
		
		$data = array( "id" => $this->get_id() );
		for ($i=0; $i<sizeof($this->config->text); $i++){
			$extra = $this->config->text[$i]["name"];
			$data[$extra]=$this->data[$extra];
		}

		if ($this->kids === true)
			$data["dhx_kids"] = 1;

		return $data;
	}

	function to_xml_end(){
		return "";
	}
}


?>

