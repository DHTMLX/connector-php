<?php
/*
	@author dhtmlx.com
	@license GPL, see license.txt
*/
require_once("treegrid_connector.php");

class TreeGridMultitableConnector extends TreeGridConnector{

	private $max_level = null;
	public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
		$data_type="TreeGridMultitableDataProcessor";
		if (!$render_type) $render_type="MultitableRenderStrategy";
		parent::__construct($res,$type,$item_type,$data_type,$render_type);
		$this->event->attach("beforeProcessing", Array($this->render, 'id_translate_before'));
		$this->event->attach("afterProcessing", Array($this->render, 'id_translate_after'));
	}

	public function render(){
		$this->dload = true;
		return parent::render();
	}

	/*! sets relation for rendering */
	protected function set_relation() {
		if (!isset($_GET['id']))
			$this->request->set_relation(false);
	}

	/*! gets resource for rendering */
	protected function get_resource() {
		return $this->sql->select($this->request);
	}

	public function xml_start(){
		if (isset($_GET['id'])) {
			return "<rows parent='".$this->render->level_id($_GET['id'], $this->render->get_level() - 1)."'>";
		} else {
			return "<rows parent=''>";
		}
	}

	/*! set maximum level of tree
		@param max_level
			maximum level
	*/
	public function setMaxLevel($max_level) {
		$this->render->set_max_level($max_level);
	}

	public function get_level() {
		return $this->render->get_level();
	}


}


class TreeGridMultitableDataProcessor extends DataProcessor {

	function name_data($data){
		if ($data=="gr_pid")
			return $this->config->relation_id["name"];
		if ($data=="gr_id")
			return $this->config->id["name"];
		preg_match('/^c([%\d]+)$/', $data, $data_num);
		if (!isset($data_num[1])) return $data;
		$data_num = $data_num[1];
		if (isset($this->config->data[$data_num]["db_name"])) {
			return $this->config->data[$data_num]["db_name"];
		}
		return $data;
	}

}

?>