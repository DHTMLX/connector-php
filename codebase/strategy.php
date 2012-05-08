<?php

class RenderStrategy {

	protected $conn = null;

	public function __construct($conn) {
		$this->conn = $conn;
	}

	/*! render from DB resultset
		@param res
			DB resultset 
		process commands, output requested data as XML
	*/
	public function render_set($res, $name, $dload, $sep){
		$output="";
		$index=0;
		$conn = $this->conn;
		$conn->event->trigger("beforeRenderSet",$conn,$res,$conn->get_config());
		while ($data=$conn->sql->get_next($res)){
			$data = new $name($data,$conn->get_config(),$index);
			if ($data->get_id()===false)
				$data->set_id($conn->uuid());
			$conn->event->trigger("beforeRender",$data);
			$output.=$data->to_xml().$sep;
			$index++;
		}
		return $output;
	}

}

class JSONRenderStrategy {

	/*! render from DB resultset
		@param res
			DB resultset 
		process commands, output requested data as json
	*/
	public function render_set($res, $name, $dload, $sep){
		$output=array();
		$index=0;
		$conn = $this->conn;
		$conn->event->trigger("beforeRenderSet",$conn,$res,$conn->get_config());
		while ($data=$conn->sql->get_next($res)){
			$data = new $name($data,$conn->get_config(),$index);
			if ($data->get_id()===false)
				$data->set_id($conn->uuid());
			$conn->event->trigger("beforeRender",$data);
			$output[]=$data->to_xml();
			$index++;
		}
		return json_encode($output);
	}

}

class TreeRenderStrategy extends RenderStrategy {

	public function render_set($res, $name, $dload, $sep){
		$output="";
		$index=0;
		$conn = $this->conn;
		while ($data=$conn->sql->get_next($res)){
			$data = new $name($data,$conn->get_config(),$index);
			$conn->event->trigger("beforeRender",$data);
			//there is no info about child elements,
			//if we are using dyn. loading - assume that it has,
			//in normal mode juse exec sub-render routine
			if ($data->has_kids()===-1 && $dload)
					$data->set_kids(true);
			$output.=$data->to_xml_start();
			if ($data->has_kids()===-1 || ( $data->has_kids()==true && !$dload)){
				$sub_request = new DataRequestConfig($conn->get_request());
				$sub_request->set_relation($data->get_id());
				$output.=$this->render_set($conn->sql->select($sub_request), $name, $dload, $sep);
			}
			$output.=$data->to_xml_end();
			$index++;
		}
		return $output;
	}

}



class JSONTreeRenderStrategy extends RenderStrategy {

	public function render_set($res, $name, $dload, $sep){
		$output=array();
		$index=0;
		$conn = $this->conn;
		while ($data=$conn->sql->get_next($res)){
			$data = new $name($data,$conn->get_config(),$index);
			$conn->event->trigger("beforeRender",$data);
			//there is no info about child elements, 
			//if we are using dyn. loading - assume that it has,
			//in normal mode just exec sub-render routine			
			if ($data->has_kids()===-1 && $dload)
					$data->set_kids(true);
			$record = $data->to_xml_start();
			if ($data->has_kids()===-1 || ( $data->has_kids()==true && !$dload)){
				$sub_request = new DataRequestConfig($conn->get_request());
				$sub_request->set_relation($data->get_id());
				$temp = $this->render_set($conn->sql->select($sub_request), $name, $dload, $sep);
				if (sizeof($temp))
					$record["data"] = $temp;
			}
			$output[] = $record;
			$index++;
		}
		return $output;
	}	

}


class MultitableRenderStrategy extends RenderStrategy {

	public function render_set($res, $name, $dload, $sep){
		$output="";
		$index=0;
		$conn = $this->conn;
		$config = $conn->get_config();
		while ($data=$conn->sql->get_next($res)){
			$data[$config->id['name']] = $conn->level_id($data[$config->id['name']]);
			$data = new $name($data,$config,$index);
			$conn->event->trigger("beforeRender",$data);
			if (($conn->getMaxLevel() !== null)&&($conn->get_level() == $conn->getMaxLevel())) {
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

}


class JSONMultitableRenderStrategy extends MultitableRenderStrategy {

	public function render_set($res, $name, $dload, $sep){
		$output=array();
		$index=0;
		$conn = $this->conn;
		$config = $conn->get_config();
		while ($data=$conn->sql->get_next($res)){
			$data[$config->id['name']] = $conn->level_id($data[$config->id['name']]);
			$data = new $name($data,$config,$index);
			$conn->event->trigger("beforeRender",$data);

			if ($conn->is_max_level()) {
				$data->set_kids(false);
			} else {
				if ($data->has_kids()===-1)
					$data->set_kids(true);
			}
			$record = $data->to_xml_start($output);
			$output[] = $record;
			$index++;
		}
		return $output;
	}

}


class GroupRenderStrategy extends RenderStrategy {

	private $id_postfix = '__{group_param}';

	public function render_set($res, $name, $dload, $sep){
		$output="";
		$index=0;
		$conn = $this->conn;
		$config = $conn->get_config();
		while ($data=$conn->sql->get_next($res)){
			if (isset($data[$config->id['name']])) {
				$has_kids = false;
			} else {
				$data[$config->id['name']] = $data['value'].$this->id_postfix;
				$data[$config->text[0]['name']] = $data['value'];
				$has_kids = true;
			}
			$data = new $name($data,$config,$index);
			$conn->event->trigger("beforeRender",$data);
			if ($has_kids === false) {
				$data->set_kids(false);
			}

			if ($data->has_kids()===-1 && $dload)
				$data->set_kids(true);
			$output.=$data->to_xml_start();
			if (($data->has_kids()===-1 || ( $data->has_kids()==true && !$dload))&&($has_kids == true)){
				$sub_request = new DataRequestConfig($conn->get_request());
				$sub_request->set_relation(str_replace($this->id_postfix, "", $data->get_id()));
				$output.=$this->render_set($conn->sql->select($sub_request), $name, $dload, $sep);
			}
			$output.=$data->to_xml_end();
			$index++;
		}
		return $output;
	}

	public function check_id($action) {
		if (isset($_GET['editing'])) {
			$config = $this->conn->get_config();
			$id = $action->get_id();
			$pid = $action->get_value($config->relation_id['name']);
			$pid = str_replace($this->id_postfix, "", $pid);
			$action->set_value($config->relation_id['name'], $pid);
			if (!empty($pid)) {
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

	public function replace_postfix() {
		if (isset($_GET['id'])) {
			$_GET['id'] = str_replace($this->id_postfix, "", $_GET['id']);
		}
	}

	public function get_postfix() {
		return $this->id_postfix;
	}

}


?>