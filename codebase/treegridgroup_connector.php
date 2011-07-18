<?php
/*
	@author dhtmlx.com
	@license GPL, see license.txt
*/
require_once("treegrid_connector.php");

class TreeGridGroupConnector extends TreeGridConnector{

	private $id_postfix = '__{group_param}';

	public function __construct($res,$type=false,$item_type=false,$data_type=false){
		parent::__construct($res,$type,$item_type,$data_type);
		$this->event->attach("beforeProcessing", Array($this, 'check_id'));
	}


	public function render(){
		if (isset($_GET['id'])) {
			$_GET['id'] = str_replace($this->id_postfix, "", $_GET['id']);
		}
		$this->parse_request();
		if (!isset($_GET['id'])) {
			$this->request->set_relation(false);
		}
		
		if (isset($_GET["editing"]))
			$this->editing=true;
		else if (isset($_POST["ids"])){
			$this->editing=true;
		} else {
			$this->editing = false;
		}
		
		if ($this->editing){
			$dp = new $this->names["data_class"]($this,$this->config,$this->request);
			$dp->process($this->config,$this->request);
		}
		else {
			$wrap = new SortInterface($this->request);
			$this->event->trigger("beforeSort",$wrap);
			$wrap->store();
			
			$wrap = new FilterInterface($this->request);
			$this->event->trigger("beforeFilter",$wrap);
			$wrap->store();
			
			if (isset($_GET['id'])) {
				$this->output_as_xml( $this->sql->select($this->request) );
			} else {
				$relation_id = $this->config->relation_id['name'];
				$this->output_as_xml( $this->sql->get_variants($this->config->relation_id['name'], $this->request));
			}
		}
		$this->end_run();
	}


	protected function render_set($res){
		$output="";
		$index=0;
		$records = Array();
		while ($data=$this->sql->get_next($res)){
			if (isset($data[$this->config->id['name']])) {
				$has_kids = false;
			} else {
				$data[$this->config->id['name']] = $data['value'].$this->id_postfix;
				$data[$this->config->text[0]['name']] = $data['value'];
				$has_kids = true;
			}
			$data = new $this->names["item_class"]($data,$this->config,$index);
			$this->event->trigger("beforeRender",$data);
			if ($has_kids === false) {
				$data->set_kids(false);
			}

			if ($data->has_kids()===-1 && $this->dload)
					$data->set_kids(true);
			$output.=$data->to_xml_start();
			if (($data->has_kids()===-1 || ( $data->has_kids()==true && !$this->dload))&&($has_kids == true)){
				$sub_request = new DataRequestConfig($this->request);
				$sub_request->set_relation(str_replace($this->id_postfix, "", $data->get_id()));
				$output.=$this->render_set($this->sql->select($sub_request));
			}
			$output.=$data->to_xml_end();
			$index++;
		}
		return $output;
	}


	/*! renders self as  xml, starting part
	*/	
	protected function xml_start(){
		if (isset($_GET['id'])) {
			return "<rows parent='".$_GET['id'].$this->id_postfix."'>";
		} else {
			return "<rows parent='0'>";
		}
	}
	
	
	public function check_id($action) {
		if (isset($_GET['editing'])) {
			$id = $action->get_id();
			$pid = $action->get_value($this->config->relation_id['name']);
			$pid = str_replace($this->id_postfix, "", $pid);
			$action->set_value($this->config->relation_id['name'], $pid);
			if (strpos($id, $this->id_postfix) == false) {
				return $action;
			} else {
				$action->error();
				$action->set_response_text("This record can't be updated!");
				return $action;
			}
		} else {
			return $action;
		}
	}
}

?>