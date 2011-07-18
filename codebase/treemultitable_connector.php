<?php
/*
	@author dhtmlx.com
	@license GPL, see license.txt
*/
require_once("tree_connector.php");

class TreeMultitableConnector extends TreeConnector{

	private $level = 0;
	private $max_level = null;

	public function __construct($res,$type=false,$item_type=false,$data_type=false){
		$data_type="TreeDataProcessor";
		parent::__construct($res,$type,$item_type,$data_type);
		$this->event->attach("beforeProcessing", Array($this, 'id_translate_before'));
		$this->event->attach("afterProcessing", Array($this, 'id_translate_after'));
	}


	public function render(){
		$this->parse_request();
		$this->dload = true;
		if ((isset($_GET["editing"]))||(isset($_POST["ids"]))) {
			$this->editing=true;
		} else {
			$this->editing = false;
		}

		if ($this->editing){
			$dp = new $this->names["data_class"]($this,$this->config,$this->request);
			$dp->process($this->config,$this->request);
		} else {
			$wrap = new SortInterface($this->request);
			$this->event->trigger("beforeSort",$wrap);
			$wrap->store();

			$wrap = new FilterInterface($this->request);
			$this->event->trigger("beforeFilter",$wrap);
			$wrap->store();

			if (isset($_GET['id'])) {
				$this->output_as_xml( $this->sql->select($this->request) );
			} else {
				$this->request->set_relation(false);
				$this->output_as_xml( $this->sql->select($this->request) );
			}
		}
		$this->end_run();
	}

	protected function render_set($res){
		$output="";
		$index=0;
		$records = Array();
		while ($data=$this->sql->get_next($res)){
			$data[$this->config->id['name']] = $this->level.'#'.$data[$this->config->id['name']];
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
		if (isset($_GET['id'])) {
			return "<tree id='".($this->level - 1).'#'.$_GET['id']."'>";
		} else {
			return "<tree id='0'>";
		}
	}


	public function get_level() {
		if (!isset($_GET['id'])) {
			if (isset($_POST['ids'])) {
				$ids = explode(",",$_POST["ids"]);
				$id = $this->parseId($ids[0]);
				$this->level--;
			}
			$this->request->set_relation(false);
		} else {
			$id = $this->parseId($_GET['id']);
			$_GET['id'] = $id;
		}
		return $this->level;
	}


	public function parseId($id, $set_level = true) {
		$result = Array();
		preg_match('/^(.+)#/', $id, $result);
		if ($set_level === true) {
			$this->level = $result[1] + 1;
		}
		preg_match('/^(.+)#(.*)$/', $id, $result);
		if (isset($result[2])) {
			$id = $result[2];
		} else {
			$id = '';
		}
		return $id;
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
		$id = $this->parseId($id, false);
		$action->set_id($id);
		$action->set_value('tr_id', $id);
		$action->set_new_id($id);
		$pid = $action->get_value($this->config->relation_id['db_name']);
		$pid = $this->parseId($pid, false);
		$action->set_value($this->config->relation_id['db_name'], $pid);
	}


	/*! add level prefix in id and new id after processing
		@param action
			DataAction object
	*/
	public function id_translate_after($action) {
		$id = $action->get_id();
		$action->set_id(($this->level).'#'.$id);
		$id = $action->get_new_id();
		$action->success(($this->level).'#'.$id);
	}

}

?>