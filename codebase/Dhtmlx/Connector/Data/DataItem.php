<?php
namespace Dhtmlx\Connector\Data;

/*! base class for component item representation
**/
class DataItem {
    protected $data; //!< hash of data
    protected $config;//!< DataConfig instance
    protected $index;//!< index of element
    protected $skip;//!< flag , which set if element need to be skiped during rendering
    protected $userdata;

    /*! constructor

        @param data
            hash of data
        @param config
            DataConfig object
        @param index
            index of element
    */
    function __construct($data,$config,$index){
        $this->config=$config;
        $this->data=$data;
        $this->index=$index;
        $this->skip=false;
        $this->userdata=false;
    }

    //set userdata for the item
    function set_userdata($name, $value){
        if ($this->userdata === false)
            $this->userdata = array();

        $this->userdata[$name]=$value;
    }
    /*! get named value

        @param name
            name or alias of field
        @return
            value from field with provided name or alias
    */
    public function get_value($name){
        return $this->data[$name];
    }
    /*! set named value

        @param name
            name or alias of field
        @param value
            value for field with provided name or alias
    */
    public function set_value($name,$value){
        return $this->data[$name]=$value;
    }
    /*! get id of element
        @return
            id of element
    */
    public function get_id(){
        $id = $this->config->id["name"];
        if (array_key_exists($id,$this->data))
            return $this->data[$id];
        return false;
    }
    /*! change id of element

        @param value
            new id value
    */
    public function set_id($value){
        $this->data[$this->config->id["name"]]=$value;
    }
    /*! get index of element

        @return
            index of element
    */
    public function get_index(){
        return $this->index;
    }
    /*! mark element for skiping ( such element will not be rendered )
    */
    public function skip(){
        $this->skip=true;
    }

    /*! return self as XML string
    */
    public function to_xml(){
        return $this->to_xml_start().$this->to_xml_end();
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

    /*! return starting tag for self as XML string
    */
    public function to_xml_start(){
        $str="<item";
        for ($i=0; $i < sizeof($this->config->data); $i++){
            $name=$this->config->data[$i]["name"];
            $db_name=$this->config->data[$i]["db_name"];
            $str.=" ".$name."='".$this->xmlentities($this->data[$name])."'";
        }
        //output custom data
        if ($this->userdata !== false)
            foreach ($this->userdata as $key => $value){
                $str.=" ".$key."='".$this->xmlentities($value)."'";
            }

        return $str.">";
    }
    /*! return ending tag for XML string
    */
    public function to_xml_end(){
        return "</item>";
    }
}