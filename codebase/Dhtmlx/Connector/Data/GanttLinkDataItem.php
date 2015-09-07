<?php
namespace Dhtmlx\Connector\Data;

class GanttLinkDataItem extends DataItem {

    public function to_xml_start(){
        $str="<item id='".$this->xmlentities($this->get_id())."'";
        for ($i=0; $i < sizeof($this->config->data); $i++){
            $name=$this->config->data[$i]["name"];
            $db_name=$this->config->data[$i]["db_name"];
            $str.=" ".$name."='".$this->xmlentities($this->data[$name])."'";
        }
        //output custom data
        if ($this->userdata !== false)
            foreach ($this->userdata as $key => $value){
                $str.=" ".$key."='".$this->xmlentities($value)."'";
            }

        return $str.">";
    }

}