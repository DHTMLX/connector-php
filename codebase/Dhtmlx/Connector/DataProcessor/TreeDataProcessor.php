<?php
namespace Dhtmlx\Connector\DataProcessor;

class TreeDataProcessor extends DataProcessor {

    function __construct($connector,$config,$request){
        parent::__construct($connector,$config,$request);
        $request->set_relation(false);
    }

    /*! convert incoming data name to valid db name
        converts c0..cN to valid field names
        @param data
            data name from incoming request
        @return
            related db_name
    */
    function name_data($data){
        if ($data=="tr_pid")
            return $this->config->relation_id["db_name"];
        if ($data=="tr_text")
            return $this->config->text[0]["db_name"];
        return $data;
    }
}