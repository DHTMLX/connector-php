<?php
namespace Dhtmlx\Connector\Output;

class JSONMultitableTreeRenderStrategy extends RenderStrategy {

    public function render_set($res, $name, $dload, $sep, $config, $mix){
        $output=array();
        $index=0;
        $conn = $this->conn;
        $this->mix($config, $mix);
        while ($data=$conn->sql->get_next($res)){
            $data = $this->complex_mix($mix, $data);
            $data[$config->id['name']] = $this->level_id($data[$config->id['name']]);
            $data = new $name($data,$config,$index);
            $conn->event->trigger("beforeRender",$data);

            if ($this->is_max_level()) {
                $data->set_kids(false);
            } else {
                if ($data->has_kids()===-1)
                    $data->set_kids(true);
            }
            $record = $data->to_xml_start($output);
            $output[] = $record;
            $index++;
        }
        $this->unmix($config, $mix);
        return $output;
    }

}
