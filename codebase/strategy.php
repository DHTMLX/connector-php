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

	private $level = 0;
	private $max_level = null;

	public function render_set($res, $name, $dload, $sep){
		$output="";
		$index=0;
		$conn = $this->conn;
		$config = $conn->get_config();
		while ($data=$conn->sql->get_next($res)){
			$data[$config->id['name']] = $this->level_id($data[$config->id['name']]);
			$data = new $name($data,$config,$index);
			$conn->event->trigger("beforeRender",$data);
			if (($this->max_level !== null)&&($conn->get_level() == $this->max_level)) {
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


	public function level_id($id, $level = null) {
		return ($level === null ? $this->level : $level).'%23'.$id;
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
		$pid = $action->get_value($this->conn->get_config()->relation_id['db_name']);
		$pid = $this->parse_id($pid, false);
		$action->set_value($this->conn->get_config()->relation_id['db_name'], $pid);
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


	public function get_level() {
		if ($this->level) return $this->level;
		if (!isset($_GET['id'])) {
			if (isset($_POST['ids'])) {
				$ids = explode(",",$_POST["ids"]);
				$id = $this->parse_id($ids[0]);
				$this->level--;
			}
			$this->conn->get_request()->set_relation(false);
		} else {
			$id = $this->parse_id($_GET['id']);
			$_GET['id'] = $id;
		}
		return $this->level;
	}


	public function is_max_level() {
		if (($this->max_level !== null) && ($this->level() >= $this->max_level))
			return true;
		return false;
	}
	
	public function set_max_level($max_level) {
		$this->max_level = $max_level;
	}

	public function parse_id($id, $set_level = true) {
		$result = Array();
		preg_match('/^(.+)((#)|(%23))/', $id, $result);
		if ($set_level === true) {
			$this->level = isset($result[1]) ? $result[1] + 1 : 0;
		}
		preg_match('/^(.+)(#|%23)(.*)$/', $id, $result);
		if (isset($result[3])) {
			$id = $result[3];
		} else {
			$id = '';
		}
		return $id;
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

			if ($this->is_max_level()) {
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