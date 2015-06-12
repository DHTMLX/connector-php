<?php
namespace Dhtmlx\Connector\Data;

/*! DataItem class for Combo component
**/
class ComboDataItem extends DataItem{
    private $selected;//!< flag of selected option

    function __construct($data,$config,$index){
        parent::__construct($data,$config,$index);

        $this->selected=false;
    }
    /*! mark option as selected
    */
    function select(){
        $this->selected=true;
    }
    /*! return self as XML string, starting part
    */
    function to_xml_start(){
        if ($this->skip) return "";

        return "<option ".($this->selected?"selected='true'":"")."value='".$this->xmlentities($this->get_id())."'><![CDATA[".$this->data[$this->config->text[0]["name"]]."]]>";
    }
    /*! return self as XML string, ending part
    */
    function to_xml_end(){
        if ($this->skip) return "";
        return "</option>";
    }
}