<?php
namespace Dhtmlx\Connector\Event;

class EventInterface {
    protected $request; ////!< DataRequestConfig instance
    public $rules=array(); //!< array of sorting rules

    /*! constructor
        creates a new interface based on existing request
        @param request
            DataRequestConfig object
    */
    public function __construct($request){
        $this->request = $request;
    }

    /*! remove all elements from collection
        */
    public function clear(){
        array_splice($rules,0);
    }
    /*! get index by name

        @param name
            name of field
        @return
            index of named field
    */
    public function index($name){
        $len = sizeof($this->rules);
        for ($i=0; $i < $len; $i++) {
            if ($this->rules[$i]["name"]==$name)
                return $i;
        }
        return false;
    }
}
