<?php
namespace Dhtmlx\Connector\Data;

class JSONCommonDataItem extends DataItem {
    /*! return self as XML string
    */
    function to_xml(){
        if ($this->skip) return false;

        $data = array(
            'id' => $this->get_id()
        );
        for ($i=0; $i<sizeof($this->config->text); $i++){
            $extra = $this->config->text[$i]["name"];
            $data[$extra]=$this->data[$extra];
            if (is_null($data[$extra]))
                $data[$extra] = "";
        }

        if ($this->userdata !== false)
            foreach ($this->userdata as $key => $value){
                if ($value === null)
                    $data[$key]="";
                $data[$key]=$value;
            }

        return $data;
    }
}
