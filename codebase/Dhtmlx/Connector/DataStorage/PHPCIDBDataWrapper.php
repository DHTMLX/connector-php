<?php
namespace Dhtmlx\Connector\DataStorage;
use Dhtmlx\Connector\Connector;
use Dhtmlx\Connector\Tools\LogMaster;
use \Exception;

class PHPCIDBDataWrapper extends DBDataWrapper {

    public function query($sql) {
        LogMaster::log($sql);
        $res = $this->connection->query($sql);

        if ($res === false)
            throw new Exception("CI - sql execution failed");

        if(is_object($res))
            return new PHPCIResultSet($res);

        return new ArrayQueryWrapper(array());
    }

    public function get_next($res) {
        return $res->next();
    }

    public function get_new_id() {
        return $this->connection->insert_id();
    }

    public function escape($str) {
        return $this->connection->escape_str($str);
    }

    public function escape_name($data) {
        return $this->connection->protect_identifiers($data);
    }
}

class PHPCIResultSet {
    private $is_result_done = false;
    private $res;
    private $start;
    private $count;

    public function __construct($res){
        if(is_bool($res))
            $this->$is_result_done = true;
        else {
            $this->res = $res;
            $this->start = $res->current_row;
            $this->count = $res->num_rows();
        }
    }

    public function next(){
        if($this->is_result_done)
            return null;

        if($this->start != $this->count)
            return $this->res->row($this->start++, "array");
        else {
            $this->res->free_result();
            return null;
        }
    }
}

?>