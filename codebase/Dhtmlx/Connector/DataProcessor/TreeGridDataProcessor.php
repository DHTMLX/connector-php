<?php
namespace Dhtmlx\Connector\DataProcessor;

/*! DataProcessor class for Grid component
**/
class TreeGridDataProcessor extends GridDataProcessor {

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

        if ($data=="gr_pid")
            return $this->config->relation_id["name"];
        else return parent::name_data($data);
    }
}