<?php
namespace Dhtmlx\Connector;
use Dhtmlx\Connector\Tools\LogMaster;

/*! Connector for the dhtmlxCombo
**/
class ComboConnector extends Connector {
    private $filter; //!< filtering mask from incoming request
    private $position; //!< position from incoming request

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
    public function __construct($res,$type=false,$item_type=false,$data_type=false){
        if (!$item_type) $item_type="Dhtmlx\\Connector\\Data\\ComboDataItem";
        parent::__construct($res,$type,$item_type,$data_type);
    }

    //parse GET scoope, all operations with incoming request must be done here
    function parse_request(){
        parent::parse_request();

        if (isset($_GET["pos"])){
            if (!$this->dload)	//not critical, so just write a log message
                LogMaster::log("Dyn loading request received, but server side was not configured to process dyn. loading. ");
            else
                $this->request->set_limit($_GET["pos"],$this->dload);
        }

        if (isset($_GET["mask"]))
            $this->request->set_filter($this->config->text[0]["db_name"],$_GET["mask"]."%","LIKE");

        LogMaster::log($this->request);
    }


    /*! renders self as  xml, starting part
    */
    public function xml_start(){
        if ($this->request->get_start())
            return "<complete add='true'>";
        else
            return "<complete>";
    }

    /*! renders self as  xml, ending part
    */
    public function xml_end(){
        return "</complete>";
    }
}