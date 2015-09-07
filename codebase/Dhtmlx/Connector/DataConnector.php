<?php
namespace Dhtmlx\Connector;

/*! Connector class for DataView
**/
class DataConnector extends Connector {

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
    */
    public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
        if (!$item_type) $item_type="Dhtmlx\\Connector\\Data\\CommonDataItem";
        if (!$data_type) $data_type="Dhtmlx\\Connector\\DataProcessor\\CommonDataProcessor";

        $this->sections = array();

        if (!$render_type) $render_type="Dhtmlx\\Connector\\Output\\RenderStrategy";
        parent::__construct($res,$type,$item_type,$data_type,$render_type);

    }

    protected $sections;
    public function add_section($name, $string){
        $this->sections[$name] = $string;
    }

    protected function parse_request_mode(){
        if (isset($_GET['action']) && $_GET["action"] != "get")
            $this->editing = true;
        else
            parent::parse_request_mode();
    }

    //parse GET scoope, all operations with incoming request must be done here
    protected function parse_request(){
        if (isset($_GET['action'])){
            $action = $_GET['action'];
            //simple request mode
            if ($action == "get"){
                //data request
                if (isset($_GET['id'])){
                    //single entity data request
                    $this->request->set_filter($this->config->id["name"],$_GET['id'],"=");
                } else {
                    //loading collection of items
                }
            } else {
                //data saving
                $this->editing = true;
            }
            parent::check_csrf();
        } else {
            if (isset($_GET['editing']) && isset($_POST['ids']))
                $this->editing = true;
            parent::parse_request();
        }

        if (isset($_GET["start"]) && isset($_GET["count"]))
            $this->request->set_limit($_GET["start"],$_GET["count"]);

    }

    /*! renders self as  xml, starting part
    */
    protected function xml_start(){
        $start = "<data";
        foreach($this->attributes as $k=>$v)
            $start .= " ".$k."='".$v."'";
        $start.= ">";

        foreach($this->sections as $k=>$v)
            $start .= "<".$k.">".$v."</".$k.">\n";
        return $start;
    }
};