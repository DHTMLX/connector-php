<?php
namespace Dhtmlx\Connector\Output;
use Dhtmlx\Connector\Data\DataItem;
use Dhtmlx\Connector\Data\GridDataItem;
use \Exception;

class RenderStrategy {

    protected $conn = null;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /*! adds mix fields into DataConfig
     *	@param config
     *		DataConfig object
     *	@param mix
     *		mix structure
     */
    protected function mix($config, $mix) {
        for ($i = 0; $i < count($mix); $i++) {
            if ($config->is_field($mix[$i]['name'])===-1) {
                $config->add_field($mix[$i]['name']);
            }
        }
    }

    /*! remove mix fields from DataConfig
     *	@param config
     *		DataConfig object
     *	@param mix
     *		mix structure
     */
    protected function unmix($config, $mix) {
        for ($i = 0; $i < count($mix); $i++) {
            if ($config->is_field($mix[$i]['name'])!==-1) {
                $config->remove_field_full($mix[$i]['name']);
            }
        }
    }

    /*! adds mix fields in item
     *	simple mix adds only strings specified by user
     *	@param mix
     *		mix structure
     *	@param data
     *		array of selected data
     */
    protected function simple_mix($mix, $data) {
        // get mix details
        for ($i = 0; $i < count($mix); $i++)
            $data[$mix[$i]["name"]] = is_object($mix[$i]["value"]) ? "" : $mix[$i]["value"];
        return $data;
    }

    /*! adds mix fields in item
     *	complex mix adds strings specified by user and results of subrequests
     *	@param mix
     *		mix structure
     *	@param data
     *		array of selected data
     */
    protected function complex_mix($mix, $data) {
        // get mix details
        for ($i = 0; $i < count($mix); $i++) {
            $mixname = $mix[$i]["name"];
            if ($mix[$i]['filter'] !== false) {
                $subconn = $mix[$i]["value"];
                $filter = $mix[$i]["filter"];

                // setting relationships
                $subconn->clear_filter();
                foreach ($filter as $k => $v)
                    if (isset($data[$v]))
                        $subconn->filter($k, $data[$v], "=");
                    else
                        throw new Exception('There was no such data field registered as: '.$k);

                $subconn->asString(true);
                $data[$mixname]=$subconn->simple_render();
                if (is_array($data[$mixname]) && count($data[$mixname]) == 1)
                    $data[$mixname] = $data[$mixname][0];
            } else {
                $data[$mixname] = $mix[$i]["value"];
            }
        }
        return $data;
    }

    /*! render from DB resultset
        @param res
            DB resultset
        process commands, output requested data as XML
    */
    public function render_set($res, $name, $dload, $sep, $config, $mix){
        $output="";
        $index=0;
        $conn = $this->conn;
        $this->mix($config, $mix);
        $conn->event->trigger("beforeRenderSet",$conn,$res,$config);

        while($data=$conn->sql->get_next($res)) {
            $data = $this->simple_mix($mix, $data);
            $data = new $name($data, $config, $index);

            if($data->get_id()===false)
                $data->set_id($conn->uuid());

            $conn->event->trigger("beforeRender", $data);
            $output.=$data->to_xml().$sep;
            $index++;
        }
        $this->unmix($config, $mix);
        return $output;
    }

}