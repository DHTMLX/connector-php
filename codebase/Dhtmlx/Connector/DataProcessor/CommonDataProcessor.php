<?php
namespace Dhtmlx\Connector\DataProcessor;
use Dhtmlx\Connector\XSSFilter\ConnectorSecurity;
use Dhtmlx\Connector\Tools\LogMaster;

class CommonDataProcessor extends DataProcessor {
    protected function get_post_values($ids){
        if (isset($_GET['action'])){
            $data = array();
            if (isset($_POST["id"])){
                $dataset = array();
                foreach($_POST as $key=>$value)
                    $dataset[$key] = ConnectorSecurity::filter($value);

                $data[$_POST["id"]] = $dataset;
            }
            else
                $data["dummy_id"] = $_POST;
            return $data;
        }
        return parent::get_post_values($ids);
    }

    protected function get_ids(){
        if (isset($_GET['action'])){
            if (isset($_POST["id"]))
                return array($_POST['id']);
            else
                return array("dummy_id");
        }
        return parent::get_ids();
    }

    protected function get_operation($rid){
        if (isset($_GET['action']))
            return $_GET['action'];
        return parent::get_operation($rid);
    }

    public function output_as_xml($results){
        if (isset($_GET['action'])){
            LogMaster::log("Edit operation finished",$results);
            ob_clean();
            $type = $results[0]->get_status();
            if ($type == "error" || $type == "invalid"){
                echo "false";
            } else if ($type=="insert"){
                echo "true\n".$results[0]->get_new_id();
            } else
                echo "true";
        } else
            return parent::output_as_xml($results);
    }
};