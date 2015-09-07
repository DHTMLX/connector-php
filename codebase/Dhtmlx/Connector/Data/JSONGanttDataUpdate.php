<?php
namespace Dhtmlx\Connector\Data;

class JSONGanttDataUpdate extends JSONDataUpdate {

    public function get_updates() {
        $updates = $this->get_data_updates();
        //ToDo: Add rendering for data.
    }


    private function get_data_updates() {
        $actions_table = $this->table;
        $version = $this->request->get_version();
        $user = $this->request->get_user();

        $select_actions = "SELECT DATAID, TYPE, USER FROM {$actions_table}";
        $select_actions .= " WHERE {$actions_table}.ID > '{$version}' AND {$actions_table}.USER <> '{$user}'";


        $output = array();
        $index = 0;
        $actions_query = $this->sql->query($select_actions);
        while($action_data=$this->sql->get_next($actions_query)){
            $action_type = $action_data["TYPE"];
            $type_parts = explode("#", $action_type);
            $action_mode = $type_parts[1];
            if($action_mode == "links") {
                $data = $this->select_links_for_action($action_data["DATAID"]);
                $data = new DataItemUpdate($data, $this->config, $index, $this->item_class);
            }
            else {
                $data = $this->select_task_for_action($action_data["DATAID"]);
                $data = new DataItemUpdate($data, $this->config, $index, $this->item_class);
            }

            array_push($output, $data);
            $index++;
        }

        return $output;
    }

    protected function select_task_for_action($taskId) {
        $tasks_table = $this->request->get_source();
        $field_task_id = $this->config->id['db_name'];
        $select_actions_tasks = "SELECT * FROM {$tasks_table} WHERE {$taskId} = {$tasks_table}.{$field_task_id}";
        return $this->sql->get_next($this->sql->query($select_actions_tasks));
    }

    protected function select_links_for_action($taskId) {
        $links_connector_options = $this->options["connector"]->get_options();
        $links_table = $links_connector_options["links"]->get_request()->get_source();
        $field_task_id = $this->config->id['db_name'];
        $select_actions_tasks = "SELECT * FROM {$links_table} WHERE {$taskId} = {$links_table}.{$field_task_id}";
        return $this->sql->get_next($this->sql->query($select_actions_tasks));
    }
}