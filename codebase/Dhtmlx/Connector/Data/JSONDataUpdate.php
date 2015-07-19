<?php
namespace Dhtmlx\Connector\Data;

class JSONDataUpdate extends DataUpdate {

    /*! adds action version in output XML as userdata
*/
    public function version_output($conn, $out) {
        $outJson = json_decode($out->__toString(), true);
        if(!isset($outJson["userdata"]))
            $outJson["userdata"] = array();

        $outJson["userdata"] = array_merge($outJson["userdata"], $this->get_version());
        $out->reset();
        $out->add(json_encode($outJson));
    }

    /*! return action version in XMl format
    */
    public function get_version() {
        $version = array("version" => $this->get_update_max_version());
        return $version;
    }

    public function get_updates() {
        $sub_request = new DataRequestConfig($this->request);
        $version = $this->request->get_version();
        $user = $this->request->get_user();

        $sub_request->parse_sql($this->select_update($this->table, $this->request->get_source(), $this->config->id['db_name'], $version, $user));
        $sub_request->set_relation(false);

        $output = $this->render_set($this->sql->select($sub_request), $this->item_class);

        if(!isset($output["userdata"]))
            $output["userdata"] = array();

        $output["userdata"] = array_merge($output["userdata"], $this->get_version());
        $this->output(json_encode($output));
    }

    protected function render_set($res, $name){
        $output = array();
        $index = 0;
        while($data = $this->sql->get_next($res)) {
            $data = new JSONDataItemUpdate($data, $this->config, $index, $name);
            $this->event->trigger("beforeRender", $data);
            array_push($output, $data->to_xml());
            $index++;
        }

        return array("updates" => $output);
    }

    protected function output($res){
        $out = new OutputWriter($res, "");
        $out->set_type("json");
        $this->event->trigger("beforeOutput", $this, $out);
        $out->output("", true, $this->encoding);
    }

}