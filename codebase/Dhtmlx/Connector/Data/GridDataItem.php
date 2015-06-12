<?php
namespace Dhtmlx\Connector\Data;

/*! DataItem class for Grid component
**/
class GridDataItem extends DataItem {
	protected $row_attrs;//!< hash of row attributes
	protected $cell_attrs;//!< hash of cell attributes

	function __construct($data,$name,$index=0){
		parent::__construct($data,$name,$index);

		$this->row_attrs=array();
		$this->cell_attrs=array();
	}
	/*! set color of row

		@param color
			color of row
	*/
	function set_row_color($color){
		$this->row_attrs["bgColor"]=$color;
	}
	/*! set style of row

		@param color
			color of row
	*/
	function set_row_style($color){
		$this->row_attrs["style"]=$color;
	}
	/*! assign custom style to the cell

		@param name
			name of column
		@param value
			css style string
	*/
	function set_cell_style($name,$value){
		$this->set_cell_attribute($name,"style",$value);
	}
	/*! assign custom class to specific cell

		@param name
			name of column
		@param value
			css class name
	*/
	function set_cell_class($name,$value){
		$this->set_cell_attribute($name,"class",$value);
	}
	/*! set custom cell attribute

		@param name
			name of column
		@param attr
			name of attribute
		@param value
			value of attribute
	*/
	function set_cell_attribute($name,$attr,$value){
		if (!array_key_exists($name, $this->cell_attrs)) $this->cell_attrs[$name]=array();
		$this->cell_attrs[$name][$attr]=$value;
	}

	/*! set custom row attribute

		@param attr
			name of attribute
		@param value
			value of attribute
	*/
	function set_row_attribute($attr,$value){
		$this->row_attrs[$attr]=$value;
	}

	/*! return self as XML string, starting part
	*/
	public function to_xml_start(){
		if ($this->skip) return "";

		$str="<row id='".$this->xmlentities($this->get_id())."'";
		foreach ($this->row_attrs as $k=>$v)
			$str.=" ".$k."='".$v."'";
		$str.=">";
		for ($i=0; $i < sizeof($this->config->text); $i++){
			$str.="<cell";
			$name=$this->config->text[$i]["name"];
			$xmlcontent = false;
			if (isset($this->cell_attrs[$name])){
				$cattrs=$this->cell_attrs[$name];
				foreach ($cattrs as $k => $v){
					$str.=" ".$k."='".$this->xmlentities($v)."'";
					if ($k == "xmlcontent")
						$xmlcontent = true;
				}
			}
			$value = isset($this->data[$name]) ? $this->data[$name] : '';
			if (!$xmlcontent)
				$str.="><![CDATA[".$value."]]></cell>";
			else
				$str.=">".$value."</cell>";
		}
		if ($this->userdata !== false)
			foreach ($this->userdata as $key => $value)
				$str.="<userdata name='".$key."'><![CDATA[".$value."]]></userdata>";

		return $str;
	}
	/*! return self as XML string, ending part
	*/
	public function to_xml_end(){
		if ($this->skip) return "";

		return "</row>";
	}
}