<?php
namespace Dhtmlx\Connector;
use Dhtmlx\Connector\Output\OutputWriter;

class JSONTreeDataMultitableConnector extends TreeDataMultitableConnector {

    public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
        if (!$item_type) $item_type="Dhtmlx\\Connector\\Data\\JSONTreeCommonDataItem";
        if (!$data_type) $data_type="Dhtmlx\\Connector\\DataProcessor\\CommonDataProcessor";
        if (!$render_type) $render_type="Dhtmlx\\Connector\\Output\\JSONMultitableTreeRenderStrategy";
        parent::__construct($res,$type,$item_type,$data_type,$render_type);
    }

    protected function output_as_xml($res){
        $result = $this->render_set($res);
        if ($this->simple) return $result;

        $data = array();
        if (isset($_GET['parent']))
            $data["parent"] = $this->render->level_id($_GET[$this->parent_name], $this->render->get_level() - 1);
        else
            $data["parent"] = "0";
        $data["data"] = $result;

        $result = json_encode($data);
        if ($this->as_string) return $result;

        $out = new OutputWriter($result, "");
        $out->set_type("json");
        $this->event->trigger("beforeOutput", $this, $out);
        $out->output("", true, $this->encoding);
    }

    public function xml_start(){
        return '';
    }
}