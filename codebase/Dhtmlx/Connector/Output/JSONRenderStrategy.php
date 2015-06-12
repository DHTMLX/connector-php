<?php
namespace Dhtmlx\Connector\Output;

class JSONRenderStrategy extends RenderStrategy {

    /*! render from DB resultset
        @param res
            DB resultset
        process commands, output requested data as json
    */
    public function render_set($res, $name, $dload, $sep, $config, $mix){
        $output=array();
        $index=0;
        $conn = $this->conn;
        $this->mix($config, $mix);
        $conn->event->trigger("beforeRenderSet",$conn,$res,$config);
        while ($data=$conn->sql->get_next($res)){
            $data = $this->complex_mix($mix, $data);
            $data = new $name($data,$config,$index);
            if ($data->get_id()===false)
                $data->set_id($conn->uuid());
            $conn->event->trigger("beforeRender",$data);
            $item = $data->to_xml();
            if ($item !== false)
                $output[]=$item;
            $index++;
        }
        $this->unmix($config, $mix);
        return $output;
    }

}
