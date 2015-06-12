<?php
namespace Dhtmlx\Connector\Output;
use Dhtmlx\Connector\Data\DataConfig;
use Dhtmlx\Connector\Data\DataRequestConfig;

class JSONTreeRenderStrategy extends TreeRenderStrategy {

    public function render_set($res, $name, $dload, $sep, $config,$mix){
        $output=array();
        $index=0;
        $conn = $this->conn;
        $config_copy = new DataConfig($config);
        $this->mix($config, $mix);
        while ($data=$conn->sql->get_next($res)){
            $data = $this->complex_mix($mix, $data);
            $data = new $name($data,$config,$index);
            $conn->event->trigger("beforeRender",$data);
            //there is no info about child elements,
            //if we are using dyn. loading - assume that it has,
            //in normal mode just exec sub-render routine
            if ($data->has_kids()===-1 && $dload)
                $data->set_kids(true);
            $record = $data->to_xml_start();
            if ($data->has_kids()===-1 || ( $data->has_kids()==true && !$dload)){
                $sub_request = new DataRequestConfig($conn->get_request());
                //$sub_request->set_fieldset(implode(",",$config_copy->db_names_list($conn->sql)));
                $sub_request->set_relation($data->get_id());
                //$sub_request->set_filters(array());
                $temp = $this->render_set($conn->sql->select($sub_request), $name, $dload, $sep, $config_copy, $mix);
                if (sizeof($temp))
                    $record["data"] = $temp;
            }
            if ($record !== false)
                $output[] = $record;
            $index++;
        }
        $this->unmix($config, $mix);
        return $output;
    }

}