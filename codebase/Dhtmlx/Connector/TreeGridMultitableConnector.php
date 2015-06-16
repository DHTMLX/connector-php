<?php
namespace Dhtmlx\Connector;

class TreeGridMultitableConnector extends TreeGridConnector {

    public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
        $data_type="Dhtmlx\\Connector\\DataProcessor\\TreeGridMultitableDataProcessor";
        if (!$render_type) $render_type="Dhtmlx\\Connector\\Output\\MultitableTreeRenderStrategy";
        parent::__construct($res,$type,$item_type,$data_type,$render_type);
    }

    public function render(){
        $this->dload = true;
        return parent::render();
    }

    /*! sets relation for rendering */
    protected function set_relation() {
        if (!isset($_GET['id']))
            $this->request->set_relation(false);
    }

    public function xml_start(){
        if (isset($_GET['id'])) {
            return "<rows parent='".$this->xmlentities($this->render->level_id($_GET['id'], $this->get_level() - 1))."'>";
        } else {
            return "<rows parent='0'>";
        }
    }

    /*! set maximum level of tree
        @param max_level
            maximum level
    */
    public function setMaxLevel($max_level) {
        $this->render->set_max_level($max_level);
    }

    public function get_level() {
        return $this->render->get_level($this->parent_name);
    }


}