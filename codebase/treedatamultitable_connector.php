<?php
/*
	@author dhtmlx.com
	@license GPL, see license.txt
*/
require_once("tree_connector.php");

class TreeDataMultitableConnector extends TreeDataConnector{

	private $level = 0;
	private $max_level = null;

	public function __construct($res,$type=false,$item_type=false,$data_type=false){
		$data_type="TreeDataProcessor";
		parent::__construct($res,$type,$item_type,$data_type);
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

	protected function render_set($res){
		$output="";
		$index=0;
		while ($data=$this->sql->get_next($res)){
			$data[$this->config->id['name']] = $this->level_id($data[$this->config->id['name']]);
			$data = new $this->names["item_class"]($data,$this->config,$index);
			$this->event->trigger("beforeRender",$data);

			if (($this->max_level !== null)&&($this->level == $this->max_level)) {
				$data->set_kids(false);
			} else {
				if ($data->has_kids()===-1)
					$data->set_kids(true);
			}
			$output.=$data->to_xml_start();
			$output.=$data->to_xml_end();
			$index++;
		}
		return $output;
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

?>