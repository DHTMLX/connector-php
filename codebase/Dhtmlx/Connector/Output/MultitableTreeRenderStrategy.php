<?php
namespace Dhtmlx\Connector\Output;

class MultitableTreeRenderStrategy extends TreeRenderStrategy {

    private $level = 0;
    private $max_level = null;
    protected $sep = "-@level@-";

    public function __construct($conn) {
        parent::__construct($conn);
        $conn->event->attach("beforeProcessing", Array($this, 'id_translate_before'));
        $conn->event->attach("afterProcessing", Array($this, 'id_translate_after'));
    }

    public function set_separator($sep) {
        $this->sep = $sep;
    }

    public function render_set($res, $name, $dload, $sep, $config, $mix){
        $output="";
        $index=0;
        $conn = $this->conn;
        $this->mix($config, $mix);
        while ($data=$conn->sql->get_next($res)){
            $data = $this->simple_mix($mix, $data);
            $data[$config->id['name']] = $this->level_id($data[$config->id['name']]);
            $data = new $name($data,$config,$index);
            $conn->event->trigger("beforeRender",$data);
            if (($this->max_level !== null)&&($conn->get_level() == $this->max_level)) {
                $data->set_kids(false);
            } else {
                if ($data->has_kids()===-1)
                    $data->set_kids(true);
            }
            $output.=$data->to_xml_start();
            $output.=$data->to_xml_end();
            $index++;
        }
        $this->unmix($config, $mix);
        return $output;
    }


    public function level_id($id, $level = null) {
        return ($level === null ? $this->level : $level).$this->sep.$id;
    }


    /*! remove level prefix from id, parent id and set new id before processing
        @param action
            DataAction object
    */
    public function id_translate_before($action) {
        $id = $action->get_id();
        $id = $this->parse_id($id, false);
        $action->set_id($id);
        $action->set_value('tr_id', $id);
        $action->set_new_id($id);
        $pid = $action->get_value($this->conn->get_config()->relation_id['db_name']);
        $pid = $this->parse_id($pid, false);
        $action->set_value($this->conn->get_config()->relation_id['db_name'], $pid);
    }


    /*! add level prefix in id and new id after processing
        @param action
            DataAction object
    */
    public function id_translate_after($action) {
        $id = $action->get_id();
        $action->set_id($this->level_id($id));
        $id = $action->get_new_id();
        $action->success($this->level_id($id));
    }


    public function get_level($parent_name) {
        if ($this->level) return $this->level;
        if (!isset($_GET[$parent_name])) {
            if (isset($_POST['ids'])) {
                $ids = explode(",",$_POST["ids"]);
                $id = $this->parse_id($ids[0]);
                $this->level--;
            }
            $this->conn->get_request()->set_relation(false);
        } else {
            $id = $this->parse_id($_GET[$parent_name]);
            $_GET[$parent_name] = $id;
        }
        return $this->level;
    }


    public function is_max_level() {
        if (($this->max_level !== null) && ($this->level >= $this->max_level))
            return true;
        return false;
    }
    public function set_max_level($max_level) {
        $this->max_level = $max_level;
    }
    public function parse_id($id, $set_level = true) {
        $parts = explode($this->sep, $id, 2);
        if (count($parts) === 2) {
            $level = $parts[0] + 1;
            $id = $parts[1];
        } else {
            $level = 0;
            $id = '';
        }
        if ($set_level) $this->level = $level;
        return $id;
    }

}