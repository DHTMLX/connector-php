<?php
namespace Dhtmlx\Connector\Data;

class JSONTreeCommonDataItem extends TreeCommonDataItem {

    /*! return self as XML string
    */
    function to_xml_start(){
        if ($this->skip) return false;

        $data = array( "id" => $this->get_id() );
        for ($i=0; $i<sizeof($this->config->text); $i++){
            $extra = $this->config->text[$i]["name"];
            if (isset($this->data[$extra]))
                $data[$extra]=$this->data[$extra];
        }

        if ($this->userdata !== false)
            foreach ($this->userdata as $key => $value)
                $data[$key]=$value;

        if ($this->kids === true)
            $data[Connector::$kids_var] = 1;

        return $data;
    }

    function to_xml_end(){
        return "";
    }

}