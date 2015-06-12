<?php
namespace Dhtmlx\Connector\Data;
use Dhtmlx\Connector\Tools\LogMaster;
use \Exception;

/*! manager of data configuration
**/
class DataConfig {
    public $id;////!< name of ID field
    public $relation_id;//!< name or relation ID field
    public $text;//!< array of text fields
    public $data;//!< array of all known fields , fields which exists only in this collection will not be included in dataprocessor's operations


    /*! converts self to the string, for logging purposes
    **/
    public function __toString(){
        $str="ID:{$this->id['db_name']}(ID:{$this->id['name']})\n";
        $str.="Relation ID:{$this->relation_id['db_name']}({$this->relation_id['name']})\n";
        $str.="Data:";
        for ($i=0; $i<sizeof($this->text); $i++)
            $str.="{$this->text[$i]['db_name']}({$this->text[$i]['name']}),";

        $str.="\nExtra:";
        for ($i=0; $i<sizeof($this->data); $i++)
            $str.="{$this->data[$i]['db_name']}({$this->data[$i]['name']}),";

        return $str;
    }

    /*! removes un-used fields from configuration
        @param name
            name of field , which need to be preserved
    */
    public function minimize($name){
        for ($i=0; $i < sizeof($this->text); $i++){
            if ($this->text[$i]["db_name"]==$name || $this->text[$i]["name"]==$name){
                $this->text[$i]["name"]="value";
                $this->data=array($this->text[$i]);
                $this->text=array($this->text[$i]);
                return;
            }
        }
        throw new Exception("Incorrect dataset minimization, master field not found.");
    }

    public function limit_fields($data){
        if (isset($this->full_field_list))
            $this->restore_fields();
        $this->full_field_list = $this->text;
        $this->text = array();

        for ($i=0; $i < sizeof($this->full_field_list); $i++) {
            if (array_key_exists($this->full_field_list[$i]["name"],$data))
                $this->text[] = $this->full_field_list[$i];
        }
    }

    public function restore_fields(){
        if (isset($this->full_field_list))
            $this->text = $this->full_field_list;
    }

    /*! initialize inner state by parsing configuration parameters

        @param id
            name of id field
        @param fields
            name of data field(s)
        @param extra
            name of extra field(s)
        @param relation
            name of relation field

    */
    public function init($id,$fields,$extra,$relation){
        $this->id	= $this->parse($id,false);
        $this->text = $this->parse($fields,true);
        $this->data	= array_merge($this->text,$this->parse($extra,true));
        $this->relation_id = $this->parse($relation,false);
    }

    /*! parse configuration string

        @param key
            key string from configuration
        @param mode
            multi names flag
        @return
            parsed field name object
    */
    private function parse($key,$mode){
        if ($mode){
            if (!$key) return array();
            $key=explode(",",$key);
            for ($i=0; $i < sizeof($key); $i++)
                $key[$i]=$this->parse($key[$i],false);
            return $key;
        }
        $key=explode("(",$key);
        $data=array("db_name"=>trim($key[0]), "name"=>trim($key[0]));
        if (sizeof($key)>1)
            $data["name"]=substr(trim($key[1]),0,-1);
        return $data;
    }

    /*! constructor
        init public collectons
        @param proto
            DataConfig object used as prototype for new one, optional
    */
    public function __construct($proto=false){
        if ($proto!==false)
            $this->copy($proto);
        else {
            $this->text=array();
            $this->data=array();
            $this->id=array("name"=>"dhx_auto_id", "db_name"=>"dhx_auto_id");
            $this->relation_id=array("name"=>"", "db_name"=>"");
        }
    }

    /*! copy properties from source object

        @param proto
            source object
    */
    public function copy($proto){
        $this->id = $proto->id;
        $this->relation_id = $proto->relation_id;
        $this->text = $proto->text;
        $this->data = $proto->data;
    }

    /*! returns list of data fields (db_names)
        @return
            list of data fields ( ready to be used in SQL query )
    */
    public function db_names_list($db){
        $out=array();
        if ($this->id["db_name"])
            array_push($out,$db->escape_name($this->id["db_name"]));
        if ($this->relation_id["db_name"])
            array_push($out,$db->escape_name($this->relation_id["db_name"]));

        for ($i=0; $i < sizeof($this->data); $i++){
            if ($this->data[$i]["db_name"]!=$this->data[$i]["name"])
                $out[]=$db->escape_name($this->data[$i]["db_name"])." as ".$this->data[$i]["name"];
            else
                $out[]=$db->escape_name($this->data[$i]["db_name"]);
        }

        return $out;
    }

    /*! add field to dataset config ($text collection)

        added field will be used in all auto-generated queries
        @param name
            name of field
        @param aliase
            aliase of field, optional
    */
    public function add_field($name,$aliase=false){
        if ($aliase===false) $aliase=$name;

        //adding to list of data-active fields
        if ($this->id["db_name"]==$name || $this->relation_id["db_name"] == $name){
            LogMaster::log("Field name already used as ID, be sure that it is really necessary.");
        }
        if ($this->is_field($name,$this->text)!=-1)
            throw new Exception('Data field already registered: '.$name);
        array_push($this->text,array("db_name"=>$name,"name"=>$aliase));

        //adding to list of all fields as well
        if ($this->is_field($name,$this->data)==-1)
            array_push($this->data,array("db_name"=>$name,"name"=>$aliase));

    }

    /*! remove field from dataset config ($text collection)

        removed field will be excluded from all auto-generated queries
        @param name
            name of field, or aliase of field
    */
    public function remove_field($name){
        $ind = $this->is_field($name);
        if ($ind==-1) throw new Exception('There was no such data field registered as: '.$name);
        array_splice($this->text,$ind,1);
        //we not deleting field from $data collection, so it will not be included in data operation, but its data still available
    }

    /*! remove field from dataset config ($text and $data collections)

        removed field will be excluded from all auto-generated queries
        @param name
            name of field, or aliase of field
    */
    public function remove_field_full($name){
        $ind = $this->is_field($name);
        if ($ind==-1) throw new Exception('There was no such data field registered as: '.$name);
        array_splice($this->text,$ind,1);

        $ind = $this->is_field($name, $this->data);
        if ($ind==-1) throw new Exception('There was no such data field registered as: '.$name);
        array_splice($this->data,$ind,1);
    }

    /*! check if field is a part of dataset

        @param name
            name of field
        @param collection
            collection, against which check will be done, $text collection by default
        @return
            returns true if field already a part of dataset, otherwise returns true
    */
    public function is_field($name,$collection = false){
        if (!$collection)
            $collection=$this->text;

        for ($i=0; $i<sizeof($collection); $i++)
            if ($collection[$i]["name"] == $name || $collection[$i]["db_name"] == $name)	return $i;
        return -1;
    }


}