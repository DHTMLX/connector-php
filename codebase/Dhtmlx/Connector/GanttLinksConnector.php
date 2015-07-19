<?php
namespace Dhtmlx\Connector;

class GanttLinksConnector extends OptionsConnector {
    protected $live_update_data_type = "Dhtmlx\\Connector\\Data\\GanttDataUpdate";

    public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
        if (!$item_type) $item_type="Dhtmlx\\Connector\\Data\\GanttLinkDataItem";
        parent::__construct($res,$type,$item_type,$data_type,$render_type);
    }
    
    public function render(){
        if (!$this->init_flag){
            $this->init_flag=true;
            return "";
        }

        $res = $this->sql->select($this->request);
        return $this->render_set($res);
    }

    public function save() {
        $dp = new $this->names["data_class"]($this,$this->config,$this->request);
        $dp->process($this->config,$this->request);
    }
}