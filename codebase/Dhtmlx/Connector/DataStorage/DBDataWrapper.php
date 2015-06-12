<?php
namespace Dhtmlx\Connector\DataStorage;
use Dhtmlx\Connector\Data\DataRequestConfig;
use Dhtmlx\Connector\Event\SortInterface;
use \Exception;

abstract class DBDataWrapper extends DataWrapper {
    private $transaction = false; //!< type of transaction
    private $sequence = false;//!< sequence name
    private $sqls = array();//!< predefined sql actions


    /*! assign named sql query
        @param name
            name of sql query
        @param data
            sql query text
    */
    public function attach($name, $data)
    {
        $name = strtolower($name);
        $this->sqls[$name] = $data;
    }

    /*! replace vars in sql string with actual values

        @param matches
            array of field name matches
        @return
            value for the var name
    */
    public function get_sql_callback($matches)
    {
        return $this->escape($this->temp->get_value($matches[1]));
    }

    public function get_sql($name, $data)
    {
        $name = strtolower($name);
        if (!array_key_exists($name, $this->sqls)) return "";


        $str = $this->sqls[$name];
        $this->temp = $data; //dirty
        $str = preg_replace_callback('|\{([^}]+)\}|', array($this, "get_sql_callback"), $str);
        unset ($this->temp); //dirty
        return $str;
    }

    public function new_record_order($action, $source)
    {
        $order = $source->get_order();
        if ($order) {
            $table = $source->get_source();
            $id = $this->config->id["db_name"];
            $idvalue = $action->get_new_id();

            $max = $this->queryOne("SELECT MAX($order) as dhx_maxvalue FROM $table");
            $dhx_maxvalue = $max["dhx_maxvalue"] + 1;

            $this->query("UPDATE $table SET $order = $dhx_maxvalue WHERE $id = $idvalue");
        }
    }

    public function order($data, $source)
    {
        //id of moved item
        $id1 = $this->escape($data->get_value("id"));
        //id of target item
        $target = $data->get_value("target");
        if (strpos($target, "next:") !== false) {
            $dropnext = true;
            $id2 = str_replace("next:", "", $target);
        } else {
            $id2 = $target;
        }
        $id2 = $this->escape($id2);


        //for tree like components we need to limit out queries to the affected branch only
        $relation_select = $relation_update = $relation_sql_out = $relation_sql = "";
        if ($this->config->relation_id["name"]) {
            $relation = $data->get_value($this->config->relation_id["name"]);
            if ($relation !== false && $relation !== "") {
                $relation_sql = " " . $this->config->relation_id["db_name"] . " = '" . $this->escape($relation) . "' AND ";
                $relation_select = $this->config->relation_id["db_name"] . " as dhx_parent, ";
                $relation_update = " " . $this->config->relation_id["db_name"] . " = '" . $this->escape($relation) . "', ";
            }
        }


        $name = $source->get_order();
        $table = $source->get_source();
        $idkey = $this->config->id["db_name"];

        $source = $this->queryOne("select $relation_select $name as dhx_index from $table where $idkey = '$id1'");
        $source_index = $source["dhx_index"] ? $source["dhx_index"] : 0;
        if ($relation_sql)
            $relation_sql_out = " " . $this->config->relation_id["db_name"] . " = '" . $this->escape($source["dhx_parent"]) . "' AND ";

        $this->query("update $table set $name = $name - 1 where $relation_sql_out $name >= $source_index");

        if ($id2 !== "") {
            $target = $this->queryOne("select $name as dhx_index from $table where $idkey = '$id2'");
            $target_index = $target["dhx_index"];
            if (!$target_index)
                $target_index = 0;
            if ($dropnext)
                $target_index += 1;
            $this->query("update $table set $name = $name + 1 where $relation_sql $name >= $target_index");
        } else {
            $target = $this->queryOne("select max($name) as dhx_index from $table");
            $target_index = ($target["dhx_index"] ? $target["dhx_index"] : 0) + 1;
        }

        $this->query("update $table set $relation_update $name = $target_index where $idkey = '$id1'");
    }

    public function insert($data, $source)
    {
        $sql = $this->insert_query($data, $source);
        $this->query($sql);
        $data->success($this->get_new_id());
    }

    public function delete($data, $source)
    {
        $sql = $this->delete_query($data, $source);
        $this->query($sql);
        $data->success();
    }

    public function update($data, $source)
    {
        $sql = $this->update_query($data, $source);
        $this->query($sql);
        $data->success();
    }

    public function select($source)
    {
        $select = $source->get_fieldset();
        if (!$select) {
            $select = $this->config->db_names_list($this);
            $select = implode(",", $select);
        }

        $where = $this->build_where($source->get_filters(), $source->get_relation());
        $sort = $this->build_order($source->get_sort_by());

        return $this->query($this->select_query($select, $source->get_source(), $where, $sort, $source->get_start(), $source->get_count()));
    }

    public function queryOne($sql)
    {
        $res = $this->query($sql);
        if ($res)
            return $this->get_next($res);
        return false;
    }

    public function get_size($source)
    {
        $count = new DataRequestConfig($source);

        $count->set_fieldset("COUNT(*) as DHX_COUNT ");
        $count->set_sort(null);
        $count->set_limit(0, 0);

        $res = $this->select($count);
        $data = $this->get_next($res);
        if (array_key_exists("DHX_COUNT", $data)) return $data["DHX_COUNT"];
        else return $data["dhx_count"]; //postgresql
    }

    public function get_variants($name, $source)
    {
        $count = new DataRequestConfig($source);
        $count->set_fieldset("DISTINCT " . $this->escape_name($name) . " as value");
        $sort = new SortInterface($source);
        $count->set_sort(null);
        for ($i = 0; $i < count($sort->rules); $i++) {
            if ($sort->rules[$i]['name'] == $name)
                $count->set_sort($sort->rules[$i]['name'], $sort->rules[$i]['direction']);
        }
        $count->set_limit(0, 0);
        return $this->select($count);
    }

    public function sequence($sec)
    {
        $this->sequence = $sec;
    }

    /*! create an sql string for filtering rules

		@param rules
			set of filtering rules
		@param relation
			name of relation id field
		@return
			sql string with filtering rules
	*/
    protected function build_where($rules,$relation=false){
        $sql=array();
        for ($i=0; $i < sizeof($rules); $i++)
            if (is_string($rules[$i]))
                array_push($sql,"(".$rules[$i].")");
            else
                if ($rules[$i]["value"]!=""){
                    if (!$rules[$i]["operation"])
                        array_push($sql,$this->escape_name($rules[$i]["name"])." LIKE '%".$this->escape($rules[$i]["value"])."%'");
                    else
                        array_push($sql,$this->escape_name($rules[$i]["name"])." ".$rules[$i]["operation"]." '".$this->escape($rules[$i]["value"])."'");
                }

        if ($relation !== false && $relation !== ""){
            $relsql = $this->escape_name($this->config->relation_id["db_name"])." = '".$this->escape($relation)."'";
            if ($relation == "0")
                $relsql = "( ".$relsql." OR ".$this->escape_name($this->config->relation_id["db_name"])." IS NULL )";

            array_push($sql,$relsql);
        }
        return implode(" AND ",$sql);
    }
    /*! convert sorting rules to sql string

        @param by
            set of sorting rules
        @return
            sql string for set of sorting rules
    */
    protected function build_order($by){
        if (!sizeof($by)) return "";
        $out = array();
        for ($i=0; $i < sizeof($by); $i++)
            if (is_string($by[$i]))
                $out[] = $by[$i];
            else if ($by[$i]["name"])
                $out[]=$this->escape_name($by[$i]["name"])." ".$by[$i]["direction"];
        return implode(",",$out);
    }

    /*! generates sql code for select operation

        @param select
            list of fields in select
        @param from
            table name
        @param where
            list of filtering rules
        @param sort
            list of sorting rules
        @param start
            start index of fetching
        @param count
            count of records to fetch
        @return
            sql string for select operation
    */
    protected function select_query($select,$from,$where,$sort,$start,$count){
        if (!$from)
            return $select;

        $sql="SELECT ".$select." FROM ".$from;
        if ($where) $sql.=" WHERE ".$where;
        if ($sort) $sql.=" ORDER BY ".$sort;
        if ($start || $count) $sql.=" LIMIT ".$start.",".$count;
        return $sql;
    }
    /*! generates update sql

        @param data
            DataAction object
        @param request
            DataRequestConfig object
        @return
            sql string, which updates record with provided data
    */
    protected function update_query($data,$request){
        $sql="UPDATE ".$request->get_source()." SET ";
        $temp=array();
        for ($i=0; $i < sizeof($this->config->text); $i++) {
            $step=$this->config->text[$i];

            if ($data->get_value($step["name"])===Null)
                $step_value ="Null";
            else
                $step_value = "'".$this->escape($data->get_value($step["name"]))."'";
            $temp[$i]= $this->escape_name($step["db_name"])."=". $step_value;
        }
        if ($relation = $this->config->relation_id["db_name"]){
            $temp[]= $this->escape_name($relation)."='".$this->escape($data->get_value($relation))."'";
        }
        $sql.=implode(",",$temp)." WHERE ".$this->escape_name($this->config->id["db_name"])."='".$this->escape($data->get_id())."'";

        //if we have limited set - set constraints
        $where=$this->build_where($request->get_filters());
        if ($where) $sql.=" AND (".$where.")";

        return $sql;
    }

    /*! generates delete sql

        @param data
            DataAction object
        @param request
            DataRequestConfig object
        @return
            sql string, which delete record
    */
    protected function delete_query($data,$request){
        $sql="DELETE FROM ".$request->get_source();
        $sql.=" WHERE ".$this->escape_name($this->config->id["db_name"])."='".$this->escape($data->get_id())."'";

        //if we have limited set - set constraints
        $where=$this->build_where($request->get_filters());
        if ($where) $sql.=" AND (".$where.")";

        return $sql;
    }

    /*! generates insert sql

        @param data
            DataAction object
        @param request
            DataRequestConfig object
        @return
            sql string, which inserts new record with provided data
    */
    protected function insert_query($data,$request){
        $temp_n=array();
        $temp_v=array();
        foreach($this->config->text as $k => $v){
            $temp_n[$k]=$this->escape_name($v["db_name"]);
            if ($data->get_value($v["name"])===Null)
                $temp_v[$k]="Null";
            else
                $temp_v[$k]="'".$this->escape($data->get_value($v["name"]))."'";
        }
        if ($relation = $this->config->relation_id["db_name"]){
            $temp_n[]=$this->escape_name($relation);
            $temp_v[]="'".$this->escape($data->get_value($relation))."'";
        }
        if ($this->sequence){
            $temp_n[]=$this->escape_name($this->config->id["db_name"]);
            $temp_v[]=$this->sequence;
        }

        $sql="INSERT INTO ".$request->get_source()."(".implode(",",$temp_n).") VALUES (".implode(",",$temp_v).")";

        return $sql;
    }

    /*! sets the transaction mode, used by dataprocessor

        @param mode
            mode name
    */
    public function set_transaction_mode($mode){
        if ($mode!="none" && $mode!="global" && $mode!="record")
            throw new Exception("Unknown transaction mode");
        $this->transaction=$mode;
    }
    /*! returns true if global transaction mode was specified
        @return
            true if global transaction mode was specified
    */
    public function is_global_transaction(){
        return $this->transaction == "global";
    }
    /*! returns true if record transaction mode was specified
        @return
            true if record transaction mode was specified
    */
    public function is_record_transaction(){
        return $this->transaction == "record";
    }


    public function begin_transaction(){
        $this->query("BEGIN");
    }
    public function commit_transaction(){
        $this->query("COMMIT");
    }
    public function rollback_transaction(){
        $this->query("ROLLBACK");
    }

    /*! exec sql string

        @param sql
            sql string
        @return
            sql result set
    */
    abstract public function query($sql);
    /*! returns next record from result set

        @param res
            sql result set
        @return
            hash of data
    */
    abstract public function get_next($res);
    /*! returns new id value, for newly inserted row
        @return
            new id value, for newly inserted row
    */
    abstract public function get_new_id();
    /*! escape data to prevent sql injections
        @param data
            unescaped data
        @return
            escaped data
    */
    abstract public function escape($data);

    /*! escape field name to prevent sql reserved words conflict
        @param data
            unescaped data
        @return
            escaped data
    */
    public function escape_name($data){
        return $data;
    }

    /*! get list of tables in the database

        @return
            array of table names
    */
    public function tables_list() {
        throw new Exception("Not implemented");
    }

    /*! returns list of fields for the table in question

        @param table
            name of table in question
        @return
            array of field names
    */
    public function fields_list($table) {
        throw new Exception("Not implemented");
    }
}