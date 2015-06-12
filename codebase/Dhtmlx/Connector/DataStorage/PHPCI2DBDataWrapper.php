<?php
namespace Dhtmlx\Connector\DataStorage;
use Dhtmlx\Connector\Connector;
use Dhtmlx\Connector\Tools\LogMaster;
use \Exception;

class PHPCI2DBDataWrapper extends DBDataWrapper {

    public function query($sql){
        LogMaster::log($sql);
        $res=$this->connection->query($sql);

        if($res === false)
            throw new Exception("CI - sql execution failed");

        if (is_object($res))
            return new PHPCIResultSet($res);

        return new ArrayQueryWrapper(array());
    }

    public function get_next($res){
        $data = $res->next();
        return $data;
    }

    public function get_new_id(){
        return $this->connection->insert_id();
    }

    public function escape($str){
        return $this->connection->escape_str($str);
    }

    public function escape_name($data){
        return $this->connection->protect_identifiers($data);
    }
}
class PHPCIResultSet {
    private $res;
    private $start;
    private $count;
    public function __construct($res){
        $this->res = $res;
        $this->start = $res->current_row;
        $this->count = $res->num_rows;
    }

    public function next(){
        if($this->start != $this->count)
            return $this->res->row($this->start++, "array");
        else {
            $this->res->free_result();
            return null;
        }
    }
}

?>