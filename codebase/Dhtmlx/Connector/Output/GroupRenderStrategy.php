<?php
namespace Dhtmlx\Connector\Output;
use Dhtmlx\Connector\Data\DataRequestConfig;

class GroupRenderStrategy extends RenderStrategy {

    protected $id_postfix = '__{group_param}';

    public function __construct($conn) {
        parent::__construct($conn);
        $conn->event->attach("beforeProcessing", Array($this, 'check_id'));
        $conn->event->attach("onInit", Array($this, 'replace_postfix'));
    }

    public function render_set($res, $name, $dload, $sep, $config, $mix, $usemix = false){
        $output="";
        $index=0;
        $conn = $this->conn;
        if ($usemix) $this->mix($config, $mix);
        while ($data=$conn->sql->get_next($res)){
            if (isset($data[$config->id['name']])) {
                $this->simple_mix($mix, $data);
                $has_kids = false;
            } else {
                $data[$config->id['name']] = $data['value'].$this->id_postfix;
                $data[$config->text[0]['name']] = $data['value'];
                $has_kids = true;
            }
            $data = new $name($data,$config,$index);
            $conn->event->trigger("beforeRender",$data);
            if ($has_kids === false) {
                $data->set_kids(false);
            }

            if ($data->has_kids()===-1 && $dload)
                $data->set_kids(true);
            $output.=$data->to_xml_start();
            if (($data->has_kids()===-1 || ( $data->has_kids()==true && !$dload))&&($has_kids == true)){
                $sub_request = new DataRequestConfig($conn->get_request());
                $sub_request->set_relation(str_replace($this->id_postfix, "", $data->get_id()));
                $output.=$this->render_set($conn->sql->select($sub_request), $name, $dload, $sep, $config, $mix, true);
            }
            $output.=$data->to_xml_end();
            $index++;
        }
        if ($usemix) $this->unmix($config, $mix);
        return $output;
    }

    public function check_id($action) {
        if (isset($_GET['editing'])) {
            $config = $this->conn->get_config();
            $id = $action->get_id();
            $pid = $action->get_value($config->relation_id['name']);
            $pid = str_replace($this->id_postfix, "", $pid);
            $action->set_value($config->relation_id['name'], $pid);
            if (!empty($pid)) {
                return $action;
            } else {
                $action->error();
                $action->set_response_text("This record can't be updated!");
                return $action;
            }
        } else {
            return $action;
        }
    }

    public function replace_postfix() {
        if (isset($_GET['id'])) {
            $_GET['id'] = str_replace($this->id_postfix, "", $_GET['id']);
        }
    }

    public function get_postfix() {
        return $this->id_postfix;
    }

}