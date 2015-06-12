<?php
namespace Dhtmlx\Connector\DataProcessor;

class KeyGridDataProcessor extends DataProcessor {

    /*! convert incoming data name to valid db name
        converts c0..cN to valid field names
        @param data
            data name from incoming request
        @return
            related db_name
    */
    function name_data($data){
        if ($data == "gr_id") return "__dummy__id__"; //ignore ID
        $parts=explode("c",$data);
        if ($parts[0]=="" && intval($parts[1])==$parts[1])
            return $this->config->text[intval($parts[1])]["name"];
        return $data;
    }

}