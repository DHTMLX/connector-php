<?php
namespace Dhtmlx\Connector\DataProcessor;

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