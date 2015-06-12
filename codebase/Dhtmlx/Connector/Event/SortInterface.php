<?php
namespace Dhtmlx\Connector\Event;

class SortInterface extends EventInterface {
    /*! constructor
        creates a new interface based on existing request
        @param request
            DataRequestConfig object
    */
    public function __construct($request){
        parent::__construct($request);
        $this->rules = &$request->get_sort_by_ref();
    }
    /*! add new sorting rule

        @param name
            name of field
        @param dir
            direction of sorting
    */
    public function add($name,$dir){
        if ($dir === false)
            $this->request->set_sort($name);
        else
            $this->request->set_sort($name,$dir);
    }
    public function store(){
        $this->request->set_sort_by($this->rules);
    }
}