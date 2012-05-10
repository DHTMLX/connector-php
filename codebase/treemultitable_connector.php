<?php
/*
	@author dhtmlx.com
	@license GPL, see license.txt
*/
require_once("tree_connector.php");

class TreeMultitableConnector extends TreeConnector{

	private $max_level = null;
	public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
		if (!$data_type) $data_type="TreeDataProcessor";
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
			return "<tree id='".($this->render->level_id($_GET['id'], $this->render->get_level() - 1))."'>";
		} else {
			return "<tree id='0'>";
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

?>