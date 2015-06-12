<?php
namespace Dhtmlx\Connector\Data;

/*! DataItem class for TreeGrid component
**/
class TreeGridDataItem extends GridDataItem {
    private $kids=-1;//!< checked state

    function __construct($data,$config,$index){
        parent::__construct($data,$config,$index);
        $this->im0=false;
    }
    /*! return id of parent record

        @return
            id of parent record
    */
    function get_parent_id(){
        return $this->data[$this->config->relation_id["name"]];
    }
    /*! assign image to treegrid's item
        longer description
        @param img
            relative path to the image
    */
    function set_image($img){
        $this->set_cell_attribute($this->config->text[0]["name"],"image",$img);
    }

    /*! return count of child items
        -1 if there is no info about childs
        @return
            count of child items
    */
    function has_kids(){
        return $this->kids;
    }
    /*! sets count of child items
        @param value
            count of child items
    */
    function set_kids($value){
        $this->kids=$value;
        if ($value)
            $this->set_row_attribute("xmlkids",$value);
    }
}