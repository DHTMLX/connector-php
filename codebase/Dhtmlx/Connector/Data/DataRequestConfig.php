<?php
namespace Dhtmlx\Connector\Data;
use \Exception;

/*! manager of data request
**/
class DataRequestConfig {

    private $action_mode = "";
    private $filters;	//!< array of filtering rules
    private $relation=false;	//!< ID or other element used for linking hierarchy
    private $sort_by;	//!< sorting field
    private $start;	//!< start of requested data
    private $count;	//!< length of requested data

    private $order = false;
    private $user;
    private $version;

    //for render_sql
    private $source;	//!< souce table or another source destination
    private $fieldset;	//!< set of data, which need to be retrieved from source

    /*! constructor

        @param proto
            DataRequestConfig object, optional, if provided then new request object will copy all properties from provided one
    */
    public function __construct($proto=false){
        if ($proto)
            $this->copy($proto);
        else{
            $start=0;
            $this->filters=array();
            $this->sort_by=array();
        }
    }

    /*! copy parameters of source object into self

        @param proto
            source object
    */
    public function copy($proto){
        $this->filters	=$proto->get_filters();
        $this->sort_by	=$proto->get_sort_by();
        $this->count	=$proto->get_count();
        $this->start	=$proto->get_start();
        $this->source	=$proto->get_source();
        $this->fieldset	=$proto->get_fieldset();
        $this->relation		=$proto->get_relation();
        $this->user = $proto->user;
        $this->version = $proto->version;
        $this->action_mode = $proto->action_mode;
    }

    /*! convert self to string ( for logs )
        @return
            self as plain string,
    */
    public function __toString(){
        $str="Source:{$this->source}\nFieldset:{$this->fieldset}\nWhere:";
        for ($i=0; $i < sizeof($this->filters); $i++)
            $str.=$this->filters[$i]["name"]." ".$this->filters[$i]["operation"]." ".$this->filters[$i]["value"].";";
        $str.="\nStart:{$this->start}\nCount:{$this->count}\n";
        for ($i=0; $i < sizeof($this->sort_by); $i++)
            $str.=$this->sort_by[$i]["name"]."=".$this->sort_by[$i]["direction"].";";
        $str.="\nRelation:{$this->relation}";
        return $str;
    }

    public function set_action_mode($action_mode) {
        $this->action_mode = $action_mode;
        return $this;
    }

    public function get_action_mode() {
        return $this->action_mode;
    }

    /*! returns set of filtering rules
        @return
            set of filtering rules
    */
    public function get_filters(){
        return $this->filters;
    }
    public function &get_filters_ref(){
        return $this->filters;
    }
    public function set_filters($data){
        $this->filters=$data;
    }


    public function get_order(){
        return $this->order;
    }
    public function set_order($order){
        $this->order = $order;
    }
    public function get_user(){
        return $this->user;
    }
    public function set_user($user){
        $this->user = $user;
    }
    public function get_version(){
        return $this->version;
    }
    public function set_version($version){
        $this->version = $version;
    }

    /*! returns list of used fields
        @return
            list of used fields
    */
    public function get_fieldset(){
        return $this->fieldset;
    }
    /*! returns name of source table
        @return
            name of source table
    */
    public function get_source(){
        return $this->source;
    }
    /*! returns set of sorting rules
        @return
            set of sorting rules
    */
    public function get_sort_by(){
        return $this->sort_by;
    }
    public function &get_sort_by_ref(){
        return $this->sort_by;
    }
    public function set_sort_by($data){
        $this->sort_by=$data;
    }

    /*! returns start index
        @return
            start index
    */
    public function get_start(){
        return $this->start;
    }
    /*! returns count of requested records
        @return
            count of requested records
    */
    public function get_count(){
        return $this->count;
    }
    /*! returns name of relation id
        @return
            relation id name
    */
    public function get_relation(){
        return $this->relation;
    }

    /*! sets sorting rule

        @param field
            name of column
        @param order
            direction of sorting
    */
    public function set_sort($field,$order=false){
        if (!$field && !$order)
            $this->sort_by=array();
        else{
            if ($order===false)
                $this->sort_by[] = $field;
            else {
                $order=strtolower($order)=="asc"?"ASC":"DESC";
                $this->sort_by[]=array("name"=>$field,"direction" => $order);
            }
        }
    }
    /*! sets filtering rule

        @param field
            name of column
        @param value
            value for filtering
        @param operation
            operation for filtering, optional , LIKE by default
    */
    public function set_filter($field,$value=false,$operation=false){
        if ($value === false)
            array_push($this->filters,$field);
        else
            array_push($this->filters,array("name"=>$field,"value"=>$value,"operation"=>$operation));
    }

    /*! sets list of used fields

        @param value
            list of used fields
    */
    public function set_fieldset($value){
        $this->fieldset=$value;
    }
    /*! sets name of source table

        @param value
            name of source table
    */
    public function set_source($value){
        if (is_string($value))
            $value = trim($value);
        $this->source = $value;
    }
    /*! sets data limits

        @param start
            start index
        @param count
            requested count of data
    */
    public function set_limit($start,$count){
        $this->start=$start;
        $this->count=$count;
    }
    /*! sets name of relation id

        @param value
            name of relation id field
    */
    public function set_relation($value){
        $this->relation=$value;
    }
    /*! parse incoming sql, to fill other properties

        @param sql
            incoming sql string
    */
    public function parse_sql($sql, $as_is = false){
        if ($as_is){
            $this->fieldset = $sql;
            return;
        }

        $sql= preg_replace("/[ \n\t]+limit[\n\t ,0-9]*$/i","",$sql);

        $data = preg_split("/[ \n\t]+\\_from\\_/i",$sql,2);
        if (count($data)!=2)
            $data = preg_split("/[ \n\t]+from/i",$sql,2);
        $this->fieldset = preg_replace("/^[\s]*select/i","",$data[0],1);

        //Ignore next type of calls
        //direct call to stored procedure without FROM
        if ((count($data) == 1) ||
            //UNION select
            preg_match("#[ \n\r\t]union[ \n\t\r]#i", $sql)){
            $this->fieldset = $sql;
            return;
        }

        $table_data = preg_split("/[ \n\t]+where/i",$data[1],2);
        /*
                if sql code contains group_by we will place all sql query in the FROM
                it will not allow to use any filtering against the query
                still it is better than just generate incorrect sql commands for any group by query
        */
        if (sizeof($table_data)>1 && !preg_match("#.*group by.*#i",$table_data[1])){ //where construction exists
            $this->set_source($table_data[0]);
            $where_data = preg_split("/[ \n\t]+order[ ]+by/i",$table_data[1],2);
            $this->filters[]=$where_data[0];
            if (sizeof($where_data)==1) return; //end of line detected
            $data=$where_data[1];
        } else {
            $table_data = preg_split("/[ \n\t]+order[ ]+by/i",$data[1],2);
            $this->set_source($table_data[0]);
            if (sizeof($table_data)==1) return; //end of line detected
            $data=$table_data[1];
        }

        if (trim($data)){ //order by construction exists
            $s_data = preg_split("/\\,/",trim($data));
            for ($i=0; $i < count($s_data); $i++) {
                $data=preg_split("/[ ]+/",trim($s_data[$i]),2);
                if (sizeof($data)>1)
                    $this->set_sort($data[0],$data[1]);
                else
                    $this->set_sort($data[0]);
            }

        }
    }
}