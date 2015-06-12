<?php
namespace Dhtmlx\Connector\Data;
use Dhtmlx\Connector\Tools\LogMaster;

/*! contain all info related to action and controls customizaton
**/
class DataAction {
	private $status; //!< cuurent status of record
	private $id;//!< id of record
	private $data;//!< data hash of record
	private $userdata;//!< hash of extra data , attached to record
	private $nid;//!< new id value , after operation executed
	private $output;//!< custom output to client side code
	private $attrs;//!< hash of custtom attributes
	private $ready;//!< flag of operation's execution
	private $addf;//!< array of added fields
	private $delf;//!< array of deleted fields


	/*! constructor

		@param status
			current operation status
		@param id
			record id
		@param data
			hash of data
	*/
	function __construct($status,$id,$data){
		$this->status=$status;
		$this->id=$id;
		$this->data=$data;
		$this->nid=$id;

		$this->output="";
		$this->attrs=array();
		$this->ready=false;

		$this->addf=array();
		$this->delf=array();
	}


	/*! add custom field and value to DB operation

		@param name
			name of field which will be added to DB operation
		@param value
			value which will be used for related field in DB operation
	*/
	function add_field($name,$value){
		LogMaster::log("adding field: ".$name.", with value: ".$value);
		$this->data[$name]=$value;
		$this->addf[]=$name;
	}
	/*! remove field from DB operation

		@param name
			name of field which will be removed from DB operation
	*/
	function remove_field($name){
		LogMaster::log("removing field: ".$name);
		$this->delf[]=$name;
	}

	/*! sync field configuration with external object

		@param slave
			SQLMaster object
		@todo
			check , if all fields removed then cancel action
	*/
	function sync_config($slave){
		foreach ($this->addf as $k => $v)
			$slave->add_field($v);
		foreach ($this->delf as $k => $v)
			$slave->remove_field($v);
	}
	/*! get value of some record's propery

		@param name
			name of record's property ( name of db field or alias )
		@return
			value of related property
	*/
	function get_value($name){
        //die(var_dump($this->data["c0"]));
		if (!array_key_exists($name,$this->data)){
			LogMaster::log("Incorrect field name used: ".$name);
			LogMaster::log("data",$this->data);
			return "";
		}
		return $this->data[$name];
	}
	/*! set value of some record's propery

		@param name
			name of record's property ( name of db field or alias )
		@param value
			value of related property
	*/
	function set_value($name,$value){
		LogMaster::log("change value of: ".$name." as: ".$value);
		$this->data[$name]=$value;
	}
	/*! get hash of data properties

		@return
			hash of data properties
	*/
	function get_data(){
		return $this->data;
	}
	/*! get some extra info attached to record
		deprecated, exists just for backward compatibility, you can use set_value instead of it
		@param name
			name of userdata property
		@return
			value of related userdata property
	*/
	function get_userdata_value($name){
		return $this->get_value($name);
	}
	/*! set some extra info attached to record
		deprecated, exists just for backward compatibility, you can use get_value instead of it
		@param name
			name of userdata property
		@param value
			value of userdata property
	*/
	function set_userdata_value($name,$value){
		return $this->set_value($name,$value);
	}
	/*! get current status of record

		@return
			string with status value
	*/
	function get_status(){
		return $this->status;
	}
	/*! assign new status to the record

		@param status
			new status value
	*/
	function set_status($status){
		$this->status=$status;
	}
	/*! set id
		@param  id
			id value
		*/
	function set_id($id) {
		$this->id = $id;
		LogMaster::log("Change id: ".$id);
	}
	/*! set id
		@param  id
			id value
		*/
	function set_new_id($id) {
		$this->nid = $id;
		LogMaster::log("Change new id: ".$id);
	}
	/*! get id of current record

		@return
			id of record
	*/
	function get_id(){
		return $this->id;
	}
	/*! sets custom response text

		can be accessed through defineAction on client side. Text wrapped in CDATA, so no extra escaping necessary
		@param text
			custom response text
	*/
	function set_response_text($text){
		$this->set_response_xml("<![CDATA[".$text."]]>");
	}
	/*! sets custom response xml

		can be accessed through defineAction on client side
		@param text
			string with XML data
	*/
	function set_response_xml($text){
		$this->output=$text;
	}
	/*! sets custom response attributes

		can be accessed through defineAction on client side
		@param name
			name of custom attribute
		@param value
			value of custom attribute
	*/
	function set_response_attribute($name,$value){
		$this->attrs[$name]=$value;
	}
	/*! check if action finished

		@return
			true if action finished, false otherwise
	*/
	function is_ready(){
		return $this->ready;
	}
	/*! return new id value

		equal to original ID normally, after insert operation - value assigned for new DB record
		@return
			new id value
	*/
	function get_new_id(){
		return $this->nid;
	}

	/*! set result of operation as error
	*/
	function error(){
		$this->status="error";
		$this->ready=true;
	}
	/*! set result of operation as invalid
	*/
	function invalid(){
		$this->status="invalid";
		$this->ready=true;
	}
	/*! confirm successful opeation execution
		@param  id
			new id value, optional
	*/
	function success($id=false){
		if ($id!==false)
			$this->nid = $id;
		$this->ready=true;
	}
	/*! convert DataAction to xml format compatible with client side dataProcessor
		@return
			DataAction operation report as XML string
	*/
	function to_xml(){
		$str="<action type='{$this->status}' sid='{$this->id}' tid='{$this->nid}' ";
		foreach ($this->attrs as $k => $v) {
			$str.=$k."='".$this->xmlentities($v)."' ";
		}
		$str.=">{$this->output}</action>";
		return $str;
	}

	/*! replace xml unsafe characters

		@param string
			string to be escaped
		@return
			escaped string
	*/
	public function xmlentities($string) {
		return str_replace( array( '&', '"', "'", '<', '>', '’' ), array( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&apos;' ), $string);
	}

	/*! convert self to string ( for logs )

		@return
			DataAction operation report as plain string
	*/
	function __toString(){
		return "action:{$this->status}; sid:{$this->id}; tid:{$this->nid};";
	}


}