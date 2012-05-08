<?php
/*
	@author dhtmlx.com
	@license GPL, see license.txt
*/
require_once("tree_connector.php");

class TreeMultitableConnector extends TreeConnector{

	private $level = 0;
	private $max_level = null;
	public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
		if (!$data_type) $data_type="TreeDataProcessor";
		if (!$render_type) $render_type="MultitableRenderStrategy";
		parent::__construct($res,$type,$item_type,$data_type,$render_type);
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

	public function xml_start(){
		if (isset($_GET['id'])) {
			return "<tree id='".($this->level - 1).'#'.$_GET['id']."'>";
		} else {
			return "<tree id='0'>";
		}
	}


	public function get_level() {
		if ($this->level) return $this->level;
		if (!isset($_GET['id'])) {
			if (isset($_POST['ids'])) {
				$ids = explode(",",$_POST["ids"]);
				$id = $this->parse_id($ids[0]);
				$this->level--;
			}
			$this->request->set_relation(false);
		} else {
			$id = $this->parse_id($_GET['id']);
			$_GET['id'] = $id;
		}
		return $this->level;
	}


	public function parse_id($id, $set_level = true) {
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


	/*! gets maximum level of tree
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

?>