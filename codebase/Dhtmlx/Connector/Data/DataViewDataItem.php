<?php
namespace Dhtmlx\Connector\Data;

/*! DataItem class for DataView component
**/
class DataViewDataItem extends DataItem {
    /*! return self as XML string
    */
    function to_xml(){
        if ($this->skip) return "";

        $str="<item id='".$this->get_id()."' >";
        for ($i=0; $i<sizeof($this->config->text); $i++){
            $extra = $this->config->text[$i]["name"];
            $str.="<".$extra."><![CDATA[".$this->data[$extra]."]]></".$extra.">";
        }
        return $str."</item>";
    }
}