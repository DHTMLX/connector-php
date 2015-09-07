<?php
namespace Dhtmlx\Connector\DataStorage\ResultHandler;

class PDOResultHandler {
	private $res;
	public function __construct($res){
		$this->res = $res;
	}
	public function next(){
		$data = $this->res->fetch(\PDO::FETCH_ASSOC);
		if (!$data){
			$this->res->closeCursor();
			return null;
		}
		return $data;
	}
}