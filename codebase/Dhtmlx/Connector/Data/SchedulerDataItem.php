<?php
namespace Dhtmlx\Connector\Data;

/*! DataItem class for Scheduler component
**/
class SchedulerDataItem extends DataItem {
    /*! return self as XML string
    */
    function to_xml(){
        if ($this->skip) return "";

        $str="<event id='".$this->get_id()."' >";
        $str.="<start_date><![CDATA[".$this->data[$this->config->text[0]["name"]]."]]></start_date>";
        $str.="<end_date><![CDATA[".$this->data[$this->config->text[1]["name"]]."]]></end_date>";
        $str.="<text><![CDATA[".$this->data[$this->config->text[2]["name"]]."]]></text>";
        for ($i=3; $i<sizeof($this->config->text); $i++){
            $extra = $this->config->text[$i]["name"];
            $str.="<".$extra."><![CDATA[".$this->data[$extra]."]]></".$extra.">";
        }
        if ($this->userdata !== false)
            foreach ($this->userdata as $key => $value)
                $str.="<".$key."><![CDATA[".$value."]]></".$key.">";

        return $str."</event>";
    }
}