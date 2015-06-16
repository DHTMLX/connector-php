<?php
namespace Dhtmlx\Connector\Data;

class TreeCommonDataItem extends CommonDataItem {

    protected $kids=-1;

    function to_xml_start(){
        $str="<item id='".$this->get_id()."' ";
        for ($i=0; $i < sizeof($this->config->text); $i++){
            $name=$this->config->text[$i]["name"];
            $str.=" ".$name."='".$this->xmlentities($this->data[$name])."'";
        }

        if ($this->userdata !== false)
            foreach ($this->userdata as $key => $value)
                $str.=" ".$key."='".$this->xmlentities($value)."'";

        if ($this->kids === true)
            $str .=" ".Connector::$kids_var."='1'";

        return $str.">";
    }

    function has_kids(){
        return $this->kids;
    }

    function set_kids($value){
        $this->kids=$value;
    }

}