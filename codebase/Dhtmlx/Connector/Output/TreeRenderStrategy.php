<?php
namespace Dhtmlx\Connector\Output;
use Dhtmlx\Connector\Data\DataConfig;
use Dhtmlx\Connector\Data\DataRequestConfig;

class TreeRenderStrategy extends RenderStrategy {

    protected $id_swap = array();

    public function __construct($conn) {
        parent::__construct($conn);
        $conn->event->attach("afterInsert",array($this,"parent_id_correction_a"));
        $conn->event->attach("beforeProcessing",array($this,"parent_id_correction_b"));
    }

    public function render_set($res, $name, $dload, $sep, $config, $mix){
        $output="";
        $index=0;
        $conn = $this->conn;
        $config_copy = new DataConfig($config);
        $this->mix($config, $mix);
        while ($data=$conn->sql->get_next($res)){
            $data = $this->simple_mix($mix, $data);
            $data = new $name($data,$config,$index);
            $conn->event->trigger("beforeRender",$data);
            //there is no info about child elements,
            //if we are using dyn. loading - assume that it has,
            //in normal mode juse exec sub-render routine
            if ($data->has_kids()===-1 && $dload)
                $data->set_kids(true);
            $output.=$data->to_xml_start();
            if ($data->has_kids()===-1 || ( $data->has_kids()==true && !$dload)){
                $sub_request = new DataRequestConfig($conn->get_request());
                //$sub_request->set_fieldset(implode(",",$config_copy->db_names_list($conn->sql)));
                $sub_request->set_relation($data->get_id());
                $output.=$this->render_set($conn->sql->select($sub_request), $name, $dload, $sep, $config_copy, $mix);
            }
            $output.=$data->to_xml_end();
            $index++;
        }
        $this->unmix($config, $mix);
        return $output;
    }

    /*! store info about ID changes during insert operation
        @param dataAction
            data action object during insert operation
    */
    public function parent_id_correction_a($dataAction){
        $this->id_swap[$dataAction->get_id()]=$dataAction->get_new_id();
    }

    /*! update ID if it was affected by previous operation
        @param dataAction
            data action object, before any processing operation
    */
    public function parent_id_correction_b($dataAction){
        $relation = $this->conn->get_config()->relation_id["db_name"];
        $value = $dataAction->get_value($relation);

        if (array_key_exists($value,$this->id_swap))
            $dataAction->set_value($relation,$this->id_swap[$value]);
    }
}