<?php
namespace Dhtmlx\Connector\DataProcessor;

/*! DataProcessor class for Gantt component
**/
class GanttDataProcessor extends DataProcessor {

    function name_data($data){
        if ($data=="start_date")
            return $this->config->text[0]["name"];
        if ($data=="id")
            return $this->config->id["name"];
        if ($data=="duration" && $this->config->text[1]["name"] == "duration")
            return $this->config->text[1]["name"];
        if ($data=="end_date" && $this->config->text[1]["name"] == "end_date")
            return $this->config->text[1]["name"];
        if ($data=="text")
            return $this->config->text[2]["name"];

        return $data;
    }

}