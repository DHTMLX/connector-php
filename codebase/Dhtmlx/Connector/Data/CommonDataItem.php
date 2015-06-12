<?php
namespace Dhtmlx\Connector\Data;

/*! DataItem class for DataView component
**/
class CommonDataItem extends DataItem {
    /*! return self as XML string
    */
    function to_xml(){
        if ($this->skip) return "";
        return $this->to_xml_start().$this->to_xml_end();
    }

    function to_xml_start(){
        $str="<item id='".$this->get_id()."' ";
        for ($i=0; $i < sizeof($this->config->text); $i++){
            $name=$this->config->text[$i]["name"];
            $str.=" ".$name."='".$this->xmlentities($this->data[$name])."'";
        }

        if ($this->userdata !== false)
            foreach ($this->userdata as $key => $value)
                $str.=" ".$key."='".$this->xmlentities($value)."'";

        return $str.">";
    }
}