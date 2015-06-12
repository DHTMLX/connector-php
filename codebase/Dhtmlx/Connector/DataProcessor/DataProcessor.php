<?php
namespace Dhtmlx\Connector\DataProcessor;
use Dhtmlx\Connector\Tools\LogMaster;
use Dhtmlx\Connector\XSSFilter\ConnectorSecurity;
use Dhtmlx\Connector\Data\DataConfig;
use Dhtmlx\Connector\Data\DataAction;
use \Exception;

/*! Base DataProcessor handling
**/
class DataProcessor {
    protected $connector;//!< Connector instance
    protected $config;//!< DataConfig instance
    protected $request;//!< DataRequestConfig instance
    static public $action_param ="!nativeeditor_status";

    /*! constructor

        @param connector
            Connector object
        @param config
            DataConfig object
        @param request
            DataRequestConfig object
    */
    function __construct($connector,$config,$request){
        $this->connector= $connector;
        $this->config=$config;
        $this->request=$request;
    }

    /*! convert incoming data name to valid db name
        redirect to Connector->name_data by default
        @param data
            data name from incoming request
        @return
            related db_name
    */
    function name_data($data){
        return $data;
    }
    /*! retrieve data from incoming request and normalize it

        @param ids
            array of extected IDs
        @return
            hash of data
    */
    protected function get_post_values($ids){
        $data=array();
        for ($i=0; $i < sizeof($ids); $i++)
            $data[$ids[$i]]=array();

        foreach ($_POST as $key => $value) {
            $details=explode("_",$key,2);
            if (sizeof($details)==1) continue;

            $name=$this->name_data($details[1]);
            $data[$details[0]][$name]=ConnectorSecurity::filter($value);
        }

        return $data;
    }
    protected function get_ids(){
        if (!isset($_POST["ids"]))
            throw new Exception("Incorrect incoming data, ID of incoming records not recognized");
        return explode(",",$_POST["ids"]);
    }

    protected function get_operation($rid){
        if (!isset($_POST[$rid."_".DataProcessor::$action_param]))
            throw new Exception("Status of record [{$rid}] not found in incoming request");
        return $_POST[$rid."_".DataProcessor::$action_param];
    }
    /*! process incoming request ( save|update|delete )
    */
    function process(){
        LogMaster::log("DataProcessor object initialized",$_POST);

        $results=array();

        $ids=$this->get_ids();
        $rows_data=$this->get_post_values($ids);
        $failed=false;

        try{
            if ($this->connector->sql->is_global_transaction())
                $this->connector->sql->begin_transaction();

            for ($i=0; $i < sizeof($ids); $i++) {
                $rid = $ids[$i];
                LogMaster::log("Row data [{$rid}]",$rows_data[$rid]);
                $status = $this->get_operation($rid);

                $action=new DataAction($status,$rid,$rows_data[$rid]);

                $results[]=$action;
                $this->inner_process($action);
            }

        } catch(Exception $e){
            LogMaster::log($e);
            $failed=true;
        }

        if ($this->connector->sql->is_global_transaction()){
            if (!$failed)
                for ($i=0; $i < sizeof($results); $i++)
                    if ($results[$i]->get_status()=="error" || $results[$i]->get_status()=="invalid"){
                        $failed=true;
                        break;
                    }
            if ($failed){
                for ($i=0; $i < sizeof($results); $i++)
                    $results[$i]->error();
                $this->connector->sql->rollback_transaction();
            }
            else
                $this->connector->sql->commit_transaction();
        }

        $this->output_as_xml($results);
    }

    /*! converts status string to the inner mode name

        @param status
            external status string
        @return
            inner mode name
    */
    protected function status_to_mode($status){
        switch($status){
            case "updated":
                return "update";
                break;
            case "inserted":
                return "insert";
                break;
            case "deleted":
                return "delete";
                break;
            default:
                return $status;
                break;
        }
    }
    /*! process data updated request received

        @param action
            DataAction object
        @return
            DataAction object with details of processing
    */
    protected function inner_process($action){

        if ($this->connector->sql->is_record_transaction())
            $this->connector->sql->begin_transaction();



        try{

            $mode = $this->status_to_mode($action->get_status());
            if (!$this->connector->access->check($mode)){
                LogMaster::log("Access control: {$mode} operation blocked");
                $action->error();
            } else {
                $check = $this->connector->event->trigger("beforeProcessing",$action);


                if (!$action->is_ready())
                    $this->check_exts($action,$mode);
                if ($mode == "insert" && $action->get_status() != "error" && $action->get_status() != "invalid")
                    $this->connector->sql->new_record_order($action, $this->request);

                $check = $this->connector->event->trigger("afterProcessing",$action);
            }

        } catch (Exception $e){
            LogMaster::log($e);
            $action->set_status("error");
            if ($action)
                $this->connector->event->trigger("onDBError", $action, $e);
        }

        if ($this->connector->sql->is_record_transaction()){
            if ($action->get_status()=="error" || $action->get_status()=="invalid")
                $this->connector->sql->rollback_transaction();
            else
                $this->connector->sql->commit_transaction();
        }

        return $action;
    }

    /*! check if some event intercepts processing, send data to DataWrapper in other case

        @param action
            DataAction object
        @param mode
            name of inner mode ( will be used to generate event names )
    */
    function check_exts($action,$mode){
        $old_config = new DataConfig($this->config);


        $this->connector->event->trigger("before".$mode,$action);
        if ($action->is_ready())
            LogMaster::log("Event code for ".$mode." processed");
        else {
            //check if custom sql defined
            $sql = $this->connector->sql->get_sql($mode,$action);
            if ($sql){
                $this->connector->sql->query($sql);
            }
            else{
                $action->sync_config($this->config);


                if ($this->connector->model && method_exists($this->connector->model, $mode)){
                    call_user_func(array($this->connector->model, $mode), $action);
                    LogMaster::log("Model object process action: ".$mode);
                }
                if (!$action->is_ready()){

                    $method=array($this->connector->sql,$mode);
                    if (!is_callable($method))
                        throw new Exception("Unknown dataprocessing action: ".$mode);
                    call_user_func($method,$action,$this->request);
                }
            }
        }
        $this->connector->event->trigger("after".$mode,$action);

        $this->config->copy($old_config);
    }

    /*! output xml response for dataprocessor

        @param  results
            array of DataAction objects
    */
    function output_as_xml($results){
        LogMaster::log("Edit operation finished",$results);
        ob_clean();
        header("Content-type:text/xml");
        echo "<?xml version='1.0' ?>";
        echo "<data>";
        for ($i=0; $i < sizeof($results); $i++)
            echo $results[$i]->to_xml();
        echo "</data>";
    }

}