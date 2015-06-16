<?php
namespace Dhtmlx\Connector;
use Dhtmlx\Connector\Output\OutputWriter;

class JSONSchedulerConnector extends SchedulerConnector {

    protected $data_separator = ",";

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
        if (!$item_type) $item_type="Dhtmlx\\Connector\\Data\\JSONSchedulerDataItem";
        if (!$data_type) $data_type="Dhtmlx\\Connector\\DataProcessor\\SchedulerDataProcessor";
        if (!$render_type) $render_type="Dhtmlx\\Connector\\Output\\JSONRenderStrategy";
        parent::__construct($res,$type,$item_type,$data_type,$render_type);
    }

    protected function xml_start() {
        return '{ "data":';
    }

    protected function xml_end() {
        $this->fill_collections();
        $end = (!empty($this->extra_output)) ? ', "collections": {'.$this->extra_output.'}' : '';
        foreach ($this->attributes as $k => $v)
            $end.=", \"".$k."\":\"".$v."\"";
        $end .= '}';
        return $end;
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
                $str[]='{"id":"'.$this->xmlentities($k).'", "value":"'.$this->xmlentities($v).'"}';
            $options=implode(",",$str);
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
            $option="\"{$name}\":[";
            if (!is_string($this->options[$name])){
                $data = json_encode($this->options[$name]->render());
                $option.=substr($data,1,-1);
            } else
                $option.=$this->options[$name];
            $option.="]";
            $options[] = $option;
        }
        $this->extra_output .= implode($this->data_separator, $options);
    }


    /*! output fetched data as XML
        @param res
            DB resultset
    */
    protected function output_as_xml($res) {
        $result = $this->render_set($res);
        if ($this->simple) return $result;

        $data=$this->xml_start().json_encode($result).$this->xml_end();

        if ($this->as_string) return $data;

        $out = new OutputWriter($data, "");
        $out->set_type("json");
        $this->event->trigger("beforeOutput", $this, $out);
        $out->output("", true, $this->encoding);
    }

}