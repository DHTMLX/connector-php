<?php
namespace Dhtmlx\Connector;
use Dhtmlx\Connector\Data\DataConfig;
use Dhtmlx\Connector\Data\DataRequestConfig;

/*! Connector for the dhtmlxgrid
**/
class GridConnector extends Connector {
	
    protected $userdata;

	/*! constructor

		Here initilization of all Masters occurs, execution timer initialized
		@param res
			db connection resource
		@param type
			string , which hold type of database ( MySQL or Postgre ), optional, instead of short DB name, full name of DataWrapper-based class can be provided
		@param item_type
			name of class, which will be used for item rendering, optional, DataItem will be used by default
		@param data_type
			name of class which will be used for dataprocessor calls handling, optional, DataProcessor class will be used by default.
	*/
	public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
        $this->userdata=false;
		
		if (!$item_type) $item_type="Dhtmlx\\Connector\\Data\\GridDataItem";
        if (!$data_type) $data_type="Dhtmlx\\Connector\\DataProcessor\\GridDataProcessor";
		if (!$render_type) $render_type="Dhtmlx\\Connector\\Output\\RenderStrategy";
		parent::__construct($res,$type,$item_type,$data_type,$render_type);
	}


	protected function parse_request(){
		parent::parse_request();

		if (isset($_GET["dhx_colls"]))
			$this->fill_collections($_GET["dhx_colls"]);
	}
	protected function resolve_parameter($name){
		if (intval($name).""==$name)
			return $this->config->text[intval($name)]["db_name"];
		return $name;
	}

	/*! replace xml unsafe characters

		@param string
			string to be escaped
		@return
			escaped string
	*/
	protected function xmlentities($string) {
		return str_replace( array( '&', '"', "'", '<', '>', '’' ), array( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&apos;' ), $string);
	}

	/*! assign options collection to the column

		@param name
			name of the column
		@param options
			array or connector object
	*/
	public function set_options($name,$options){
		if (is_array($options)){
			$str="";
			foreach($options as $k => $v)
				$str.="<item value='".$this->xmlentities($k)."' label='".$this->xmlentities($v)."' />";
			$options=$str;
		}
		$this->options[$name]=$options;
	}
	/*! generates xml description for options collections

		@param list
			comma separated list of column names, for which options need to be generated
	*/
	protected function fill_collections($list=""){
		$names=explode(",",$list);
		for ($i=0; $i < sizeof($names); $i++) {
			$name = $this->resolve_parameter($names[$i]);
			if (!array_key_exists($name,$this->options)){
				$this->options[$name] = new DistinctOptionsConnector($this->get_connection(),$this->names["db_class"]);
				$c = new DataConfig($this->config);
				$r = new DataRequestConfig($this->request);
				$c->minimize($name);

				$this->options[$name]->render_connector($c,$r);
			}

			$this->extra_output.="<coll_options for='{$names[$i]}'>";
			if (!is_string($this->options[$name]))
				$this->extra_output.=$this->options[$name]->render();
			else
				$this->extra_output.=$this->options[$name];
			$this->extra_output.="</coll_options>";
		}
	}

	/*! renders self as  xml, starting part
	*/
	protected function xml_start(){
		$attributes = "";
		foreach($this->attributes as $k=>$v)
			$attributes .= " ".$k."='".$v."'";

		if ($this->dload){
			if ($pos=$this->request->get_start())
				$str = "<rows pos='".$pos."'".$attributes.">";
			else
				$str = "<rows total_count='".$this->sql->get_size($this->request)."'".$attributes.">";
		}
		else
			$str = "<rows".$attributes.">";
		
		if ($this->userdata !== false) {
			foreach ($this->userdata as $key => $value) {
				$str .= "<userdata name='".$key."'><![CDATA[".$value."]]></userdata>";				
			}			
		}

		return $str;
	}


	/*! renders self as  xml, ending part
	*/
	protected function xml_end(){
		return $this->extra_output."</rows>";
	}

    //set global userdata for grid
    public function set_userdata($name, $value){
        if ($this->userdata === false)
            $this->userdata = array();

        $this->userdata[$name]=$value;
    }
    
	public function set_config($config = false){
		if (gettype($config) == 'boolean')
			$config = new GridConfiguration($config);

		$this->event->attach("beforeOutput", Array($config, "attachHeaderToXML"));
		$this->event->attach("onInit", Array($config, "defineOptions"));
	}
}