<?php
namespace Dhtmlx\Connector\Data;

/*! DataItem class for Gantt component
**/
class GanttDataItem extends DataItem {

    /*! return self as XML string
    */
    function to_xml(){
        if ($this->skip) return "";

        $str="<task id='".$this->get_id()."' >";
        $str.="<start_date><![CDATA[".$this->data[$this->config->text[0]["name"]]."]]></start_date>";
        $str.="<".$this->config->text[1]["name"]."><![CDATA[".$this->data[$this->config->text[1]["name"]]."]]></".$this->config->text[1]["name"].">";
        $str.="<text><![CDATA[".$this->data[$this->config->text[2]["name"]]."]]></text>";
        for ($i=3; $i<sizeof($this->config->text); $i++){
            $extra = $this->config->text[$i]["name"];
            $str.="<".$extra."><![CDATA[".$this->data[$extra]."]]></".$extra.">";
        }
        if ($this->userdata !== false)
            foreach ($this->userdata as $key => $value)
                $str.="<".$key."><![CDATA[".$value."]]></".$key.">";

        return $str."</task>";
    }
}