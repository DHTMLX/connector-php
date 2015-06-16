<?php
namespace Dhtmlx\Connector;

class KeyGridConnector extends GridConnector {
    public function __construct($res,$type=false,$item_type=false,$data_type=false){
        if (!$item_type) $item_type="Dhtmlx\\Connector\\Data\\GridDataItem";
        if (!$data_type) $data_type="Dhtmlx\\Connector\\DataProcessor\\KeyGridDataProcessor";
        parent::__construct($res,$type,$item_type,$data_type);

        $this->event->attach("beforeProcessing",array($this,"before_check_key"));
        $this->event->attach("afterProcessing",array($this,"after_check_key"));
    }

    public function before_check_key($action){
        if ($action->get_value($this->config->id["name"])=="")
            $action->error();
    }
    public function after_check_key($action){
        if ($action->get_status()=="inserted" || $action->get_status()=="updated"){
            $action->success($action->get_value($this->config->id["name"]));
            $action->set_status("inserted");
        }
    }
};