<?php
namespace Dhtmlx\Connector\DataProcessor;

/*! DataProcessor class for Grid component
**/
class GridDataProcessor extends DataProcessor {

	/*! convert incoming data name to valid db name
		converts c0..cN to valid field names
		@param data
			data name from incoming request
		@return
			related db_name
	*/
	function name_data($data){
		if ($data == "gr_id") return $this->config->id["name"];
		$parts=explode("c",$data);
		if ($parts[0]=="" && ((string)intval($parts[1]))==$parts[1])
			if (sizeof($this->config->text)>intval($parts[1]))
				return $this->config->text[intval($parts[1])]["name"];
		return $data;
	}
}