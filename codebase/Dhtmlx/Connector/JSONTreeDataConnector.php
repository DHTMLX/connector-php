<?php
namespace Dhtmlx\Connector;
use Dhtmlx\Connector\Output\OutputWriter;

class JSONTreeDataConnector extends TreeDataConnector {

    public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
        if (!$item_type) $item_type="Dhtmlx\\Connector\\Data\\JSONTreeCommonDataItem";
        if (!$data_type) $data_type="Dhtmlx\\Connector\\DataProcessor\\CommonDataProcessor";
        if (!$render_type) $render_type="Dhtmlx\\Connector\\Output\\JSONTreeRenderStrategy";
        parent::__construct($res,$type,$item_type,$data_type,$render_type);
    }

    protected function output_as_xml($res){
        $result = $this->render_set($res);
        if ($this->simple) return $result;

        $data = array();
        if (!$this->rootId || $this->rootId != $this->request->get_relation())
            $data["parent"] = $this->request->get_relation();

        $data["data"] = $result;

        $this->fill_collections();
        if (!empty($this->options))
            $data["collections"] = $this->options;


        foreach($this->attributes as $k=>$v)
            $data[$k] = $v;

        $data = json_encode($data);

        // return as string
        if ($this->as_string) return $data;

        // output direct to response
        $out = new OutputWriter($data, "");
        $out->set_type("json");
        $this->event->trigger("beforeOutput", $this, $out);
        $out->output("", true, $this->encoding);
    }

    /*! assign options collection to the column

		@param name
			name of the column
		@param options
			array or connector object
	*/
    public function set_options($name,$options){
        if (is_array($options)){
            $str=array();
            foreach($options as $k => $v)
                $str[]=Array("id"=>$this->xmlentities($k), "value"=>$this->xmlentities($v));//'{"id":"'.$this->xmlentities($k).'", "value":"'.$this->xmlentities($v).'"}';
            $options=$str;
        }
        $this->options[$name]=$options;
    }

    /*! generates xml description for options collections

        @param list
            comma separated list of column names, for which options need to be generated
    */
    protected function fill_collections($list=""){
        $options = array();
        foreach ($this->options as $k=>$v) {
            $name = $k;
            if (!is_array($this->options[$name]))
                $option=$this->options[$name]->render();
            else
                $option=$this->options[$name];
            $options[$name] = $option;
        }
        $this->options = $options;
        $this->extra_output .= "'collections':".json_encode($options);
    }

}