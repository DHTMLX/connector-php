<?php
namespace Dhtmlx\Connector;
use Dhtmlx\Connector\Output\OutputWriter;

class JSONDataConnector extends DataConnector {

    public function __construct($res,$type=false,$item_type=false,$data_type=false,$render_type=false){
        if (!$item_type) $item_type="Dhtmlx\\Connector\\Data\\JSONCommonDataItem";
        if (!$data_type) $data_type="Dhtmlx\\Connector\\DataProcessor\\CommonDataProcessor";
        if (!$render_type) $render_type="Dhtmlx\\Connector\\Output\\JSONRenderStrategy";
        $this->data_separator = ",\n";
        parent::__construct($res,$type,$item_type,$data_type,$render_type);
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
            if (!is_string($this->options[$name]))
                $option.=substr(json_encode($this->options[$name]->render()),1,-1);
            else
                $option.=$this->options[$name];
            $option.="]";
            $options[] = $option;
        }
        $this->extra_output .= implode($this->data_separator, $options);
    }

    protected function resolve_parameter($name){
        if (intval($name).""==$name)
            return $this->config->text[intval($name)]["db_name"];
        return $name;
    }

    protected function output_as_xml($res){
        $json = $this->render_set($res);
        if ($this->simple) return $json;
        $result = json_encode($json);

        $this->fill_collections();
        $is_sections = sizeof($this->sections) && $this->is_first_call();
        if ($this->dload || $is_sections || sizeof($this->attributes) || !empty($this->extra_data)){

            $attributes = "";
            foreach($this->attributes as $k=>$v)
                $attributes .= ", \"".$k."\":\"".$v."\"";

            $extra = "";
            if (!empty($this->extra_output))
                $extra .= ', "collections": {'.$this->extra_output.'}';

            $sections = "";
            if ($is_sections){
                //extra sections
                foreach($this->sections as $k=>$v)
                    $sections .= ", \"".$k."\":".$v;
            }

            $dyn = "";
            if ($this->dload){
                //info for dyn. loadin
                if ($pos=$this->request->get_start())
                    $dyn .= ", \"pos\":".$pos;
                else
                    $dyn .= ", \"pos\":0, \"total_count\":".$this->sql->get_size($this->request);
            }
            if ($attributes || $sections || $this->extra_output || $dyn) {
                $result = "{ \"data\":".$result.$attributes.$extra.$sections.$dyn."}";
            }
        }

        // return as string
        if ($this->as_string) return $result;

        // output direct to response
        $out = new OutputWriter($result, "");
        $out->set_type("json");
        $this->event->trigger("beforeOutput", $this, $out);
        $out->output("", true, $this->encoding);
        return null;
    }
}
