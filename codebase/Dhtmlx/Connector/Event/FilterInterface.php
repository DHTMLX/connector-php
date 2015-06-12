<?php
namespace Dhtmlx\Connector\Event;

/*! Wrapper for collection of filtering rules
**/
class FilterInterface extends EventInterface {
    /*! constructor
        creates a new interface based on existing request
        @param request
            DataRequestConfig object
    */
    public function __construct($request){
        $this->request = $request;
        $this->rules = &$request->get_filters_ref();
    }
    /*! add new filatering rule

        @param name
            name of field
        @param value
            value to filter by
        @param rule
            filtering rule
    */
    public function add($name,$value,$rule){
        $this->request->set_filter($name,$value,$rule);
    }
    public function store(){
        $this->request->set_filters($this->rules);
    }
}