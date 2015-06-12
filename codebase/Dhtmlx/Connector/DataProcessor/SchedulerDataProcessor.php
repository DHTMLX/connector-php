<?php
namespace Dhtmlx\Connector\DataProcessor;

/*! DataProcessor class for Scheduler component
**/
class SchedulerDataProcessor extends DataProcessor {

    function name_data($data){
        if ($data=="start_date")
            return $this->config->text[0]["db_name"];
        if ($data=="id")
            return $this->config->id["db_name"];
        if ($data=="end_date")
            return $this->config->text[1]["db_name"];
        if ($data=="text")
            return $this->config->text[2]["db_name"];

        return $data;
    }

}