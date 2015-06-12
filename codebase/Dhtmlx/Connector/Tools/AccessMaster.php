<?php
namespace Dhtmlx\Connector\Tools;

/*! Class which handles access rules.
**/
class AccessMaster {
    private $rules,$local;
    /*! constructor

        Set next access right to "allowed" by default : read, insert, update, delete
        Basically - all common data operations allowed by default
    */
    function __construct(){
        $this->rules=array("read" => true, "insert" => true, "update" => true, "delete" => true);
        $this->local=true;
    }
    /*! change access rule to "allow"
        @param name
            name of access right
    */
    public function allow($name){
        $this->rules[$name]=true;
    }
    /*! change access rule to "deny"

        @param name
            name of access right
    */
    public function deny($name){
        $this->rules[$name]=false;
    }

    /*! change all access rules to "deny"
    */
    public function deny_all(){
        $this->rules=array();
    }

    /*! check access rule

        @param name
            name of access right
        @return
            true if access rule allowed, false otherwise
    */
    public function check($name){
        if ($this->local){
            /*!
            todo
                add referrer check, to prevent access from remote points
            */
        }
        if (!isset($this->rules[$name]) || !$this->rules[$name]){
            return false;
        }
        return true;
    }
}