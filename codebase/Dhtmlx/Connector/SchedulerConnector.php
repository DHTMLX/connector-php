<?php
namespace Dhtmlx\Connector;

/*! Connector class for dhtmlxScheduler
**/
class SchedulerConnector extends Connector {

    protected $extra_output="";//!< extra info which need to be sent to client side
    protected $options=array();//!< hash of OptionsConnector


    /*! assign options collection to the column

        @param name
            name of the column
        @param options
            array or connector object
    */
    public function set_options($name,$options){
        if (is_array($options)){
            $str="";
            foreach($options as $k => $v)
                $str.="<item value='".$this->xmlentities($k)."' label='".$this->xmlentities($v)."' />";
            $options=$str;
        }
        $this->options[$name]=$options;
    }


    /*! constructor

        Here initilization of all Masters occurs, execution timer initialized
        @param res
            db connection resource
        @param type
            string , which hold type of database ( MySQL or Postgre ), optional, instead of short DB name, full name of DataWrapper-based class can be provided
        @param item_type
            name of class, which will be used for item rendering, optional, DataItem will be used by default
        @param data_type
            name of class which will be used for dataprocessor calls handling, optional, DataProcessor class will be used by default.
     * @param render_type
            name of class which will be used for rendering.
    */
    public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false) {
        if (!$item_type) $item_type="Dhtmlx\\Connector\\Data\\SchedulerDataItem";
        if (!$data_type) $data_type="Dhtmlx\\Connector\\DataProcessor\\SchedulerDataProcessor";
        if (!$render_type) $render_type="Dhtmlx\\Connector\\Output\\RenderStrategy";
        parent::__construct($res,$type,$item_type,$data_type,$render_type);
    }

    //parse GET scoope, all operations with incoming request must be done here
    function parse_request(){
        parent::parse_request();
        if (count($this->config->text)){
            if (isset($_GET["to"]))
                $this->request->set_filter($this->config->text[0]["name"],$_GET["to"],"<");
            if (isset($_GET["from"]))
                $this->request->set_filter($this->config->text[1]["name"],$_GET["from"],">");
        }
    }
}