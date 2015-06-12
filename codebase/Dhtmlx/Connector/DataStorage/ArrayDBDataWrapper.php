<?php
namespace Dhtmlx\Connector\DataStorage;
use \Exception;

class ArrayDBDataWrapper extends DBDataWrapper {
    public function get_next($res)
    {
        if ($res->index < sizeof($res->data))
            return $res->data[$res->index++];
    }

    public function select($sql)
    {
        if ($this->config->relation_id["db_name"] == "") {
            if ($sql->get_relation() == "0" || $sql->get_relation() == "") {
                return new ArrayQueryWrapper($this->connection);
            } else {
                return new ArrayQueryWrapper(array());
            }
        }

        $relation_id = $this->config->relation_id["db_name"];

        for ($i = 0; $i < count($this->connection); $i++) {
            $item = $this->connection[$i];
            if (!isset($item[$relation_id])) continue;
            if ($item[$relation_id] == $sql->get_relation())
                $result[] = $item;

        }

        return new ArrayQueryWrapper($result);
    }

    public function query($sql)
    {
        throw new Exception("Not implemented");
    }

    public function escape($value)
    {
        throw new Exception("Not implemented");
    }

    public function get_new_id()
    {
        throw new Exception("Not implemented");
    }
}