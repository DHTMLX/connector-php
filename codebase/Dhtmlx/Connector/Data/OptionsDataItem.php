<?php
namespace Dhtmlx\Connector\Data;

class OptionsDataItem extends DataItem {
    function to_xml(){
        if ($this->skip) return "";
        $str ="";
        
        $str .= "<item value=\"".$this->xmlentities($this->data[$this->config->data[0]['db_name']])."\" label=\"".$this->xmlentities($this->data[$this->config->data[1]['db_name']])."\" />";
        return $str;
    }
}