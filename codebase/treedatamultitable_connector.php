<?php
/*
	@author dhtmlx.com
	@license GPL, see license.txt
*/
require_once("tree_connector.php");

class TreeDataMultitableConnector extends TreeDataConnector{

	protected $level = 0;
	protected $max_level = null;

	public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
		if (!$data_type) $data_type="TreeDataProcessor";
		if (!$render_type) $render_type="MultitableRenderStrategy";
		parent::__construct($res,$type,$item_type,$data_type,$render_type);
		$this->event->attach("beforeProcessing", Array($this, 'id_translate_before'));
		$this->event->attach("afterProcessing", Array($this, 'id_translate_after'));
	}


	//parse GET scoope, all operations with incoming request must be done here
	protected function parse_request(){
		parent::parse_request();

		if (isset($_GET["parent"]))
			$this->request->set_relation($this->parse_id($_GET["parent"], true));
		else
			$this->request->set_relation("0");
	}

	public function render(){
		$this->event->trigger("onInit", $this);
		EventMaster::trigger_static("connectorInit",$this);
		
		$this->parse_request();
		$this->dload = true;
		
		if ($this->live_update !== false && $this->updating!==false) {
			$this->live_update->get_updates();
		} else {
			if ($this->editing){
				$dp = new $this->names["data_class"]($this,$this->config,$this->request);
				$dp->process($this->config,$this->request);
			}
			else {
				if (!$this->access->check("read")){
					LogMaster::log("Access control: read operation blocked");
					echo "Access denied";
					die();
				}
				$wrap = new SortInterface($this->request);
				$this->event->trigger("beforeSort",$wrap);
				$wrap->store();
				
				$wrap = new FilterInterface($this->request);
				$this->event->trigger("beforeFilter",$wrap);
				$wrap->store();
				
				if (!isset($_GET['parent']))
					$this->request->set_relation(false);
				$this->output_as_xml( $this->sql->select($this->request) );
			}
		}
		$this->end_run();
	}

	public function xml_start(){
		if (isset($_GET['parent'])) {
			return "<data parent='".$_GET['parent']."'>";
		} else {
			return "<data parent='0'>";
		}
	}


	public function get_level() {
		if (isset($_GET['parent']))
			$this->parse_id($_GET['parent']);
		else if (isset($_POST['ids'])) {
			$ids = explode(",",$_POST["ids"]);
			if (isset($ids[0])) $this->parse_id($ids[0]);
			$this->level -= 1;
		}
		return $this->level;
	}

	public function parse_id($id, $set_level = true) {
		$parts = explode('#', $id);
		if (count($parts) === 2) {
			$level = $parts[0] + 1;
			$id = $parts[1];
		} else {
			$level = 0;
			$id = '';
		}
		if ($set_level) $this->level = $level;
		return $id;
	}

	public function level_id($id) {
		return $this->level.'#'.$id;
	}

	/*! set maximum level of tree
		@param max_level
			maximum level
	*/
	public function setMaxLevel($max_level) {
		$this->max_level = $max_level;
	}


	/*! gets maximum level of tree data
	*/
	public function getMaxLevel() {
		return $this->max_level;
	}


	public function is_max_level() {
		if (($this->max_level !== null) && ($this->level >= $this->max_level))
			return true;
		return false;
	}


	/*! remove level prefix from id, parent id and set new id before processing
		@param action
			DataAction object
	*/
	public function id_translate_before($action) {
		$id = $action->get_id();
		$id = $this->parse_id($id, false);
		$action->set_id($id);
		$action->set_value('tr_id', $id);
		$action->set_new_id($id);
		$pid = $action->get_value($this->config->relation_id['db_name']);
		$pid = $this->parse_id($pid, false);
		$action->set_value($this->config->relation_id['db_name'], $pid);
	}


	/*! add level prefix in id and new id after processing
		@param action
			DataAction object
	*/
	public function id_translate_after($action) {
		$id = $action->get_id();
		$action->set_id($this->level_id($id));
		$id = $action->get_new_id();
		$action->success($this->level_id($id));
	}

}






class JSONTreeDataMultitableConnector extends TreeDataMultitableConnector{

	public function __construct($res,$type=false,$item_type=false,$data_type=false){
		if (!$item_type) $item_type="JSONTreeCommonDataItem";
		if (!$data_type) $data_type="CommonDataProcessor";
		if (!$render_type) $render_type="JSONMultitableRenderStrategy";
		parent::__construct($res,$type,$item_type,$data_type,$render_type);
	}

	protected function output_as_xml($res){
		$data = array();
		$data["parent"] = isset($_GET['parent']) ? $_GET['parent'] : '0';
		$data["data"] = $this->render_set($res);

		$out = new OutputWriter(json_encode($data), "");
		$out->set_type("json");
		$this->event->trigger("beforeOutput", $this, $out);
		$out->output("", true, $this->encoding);
	}

	public function xml_start(){
		return '';
	}
}


?>