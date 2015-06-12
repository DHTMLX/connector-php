<?php
namespace Dhtmlx\Connector\Data;

/*! DataItem class for dhxForm component
**/
class FormDataItem extends DataItem {
    /*! return self as XML string
    */
    function to_xml(){
        if ($this->skip) return "";
        $str="";
        for ($i = 0; $i < count($this->config->data); $i++) {
            $str .= "<".$this->config->data[$i]['name']."><![CDATA[".$this->data[$this->config->data[$i]['name']]."]]></".$this->config->data[$i]['name'].">";
        }
        return $str;
    }
}