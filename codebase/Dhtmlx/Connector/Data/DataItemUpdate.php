<?php
namespace Dhtmlx\Connector\Data;
use Dhtmlx\Connector\Tools\LogMaster;

/*
	@author dhtmlx.com
	@license GPL, see license.txt
*/

/*! DataItemUpdate class for realization Optimistic concurrency control
	Wrapper for DataItem object
	It's used during outputing updates instead of DataItem object
	Create wrapper for every data item with update information.
*/

class DataItemUpdate extends DataItem {


    /*! constructor
        @param data
            hash of data
        @param config
            DataConfig object
        @param index
            index of element
    */
    public function __construct($data,$config,$index,$type){
        $this->config=$config;
        $this->data=$data;
        $this->index=$index;
        $this->skip=false;
        $this->child = new $type($data, $config, $index);
    }

    /*! returns parent_id (for Tree and TreeGrid components)
    */
    public function get_parent_id(){
        if (method_exists($this->child, 'get_parent_id')) {
            return $this->child->get_parent_id();
        } else {
            return '';
        }
    }


    /*! generate XML on the data hash base
    */
    public function to_xml(){
        $str= "<update ";
        $str .= 'status="'.$this->data['action_table_type'].'" ';
        $str .= 'id="'.$this->data['dataId'].'" ';
        $str .= 'parent="'.$this->get_parent_id().'"';
        $str .= '>';
        $str .= $this->child->to_xml();
        $str .= '</update>';
        return $str;
    }

    /*! return starting tag for XML string
    */
    public function to_xml_start(){
        $str="<update ";
        $str .= 'status="'.$this->data['action_table_type'].'" ';
        $str .= 'id="'.$this->data['dataId'].'" ';
        $str .= 'parent="'.$this->get_parent_id().'"';
        $str .= '>';
        $str .= $this->child->to_xml_start();
        return $str;
    }

    /*! return ending tag for XML string
    */
    public function to_xml_end(){
        $str = $this->child->to_xml_end();
        $str .= '</update>';
        return $str;
    }

    /*! returns false for outputing only current item without child items
    */
    public function has_kids(){
        return false;
    }

    /*! sets count of child items
        @param value
            count of child items
    */
    public function set_kids($value){
        if (method_exists($this->child, 'set_kids')) {
            $this->child->set_kids($value);
        }
    }

    /*! sets attribute for item
    */
    public function set_attribute($name, $value){
        if (method_exists($this->child, 'set_attribute')) {
            LogMaster::log("setting attribute: \nname = {$name}\nvalue = {$value}");
            $this->child->set_attribute($name, $value);
        } else {
            LogMaster::log("set_attribute method doesn't exists");
        }
    }
}