<?php
namespace Dhtmlx\Connector;

class JSONGanttLinksConnector extends JSONOptionsConnector {
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