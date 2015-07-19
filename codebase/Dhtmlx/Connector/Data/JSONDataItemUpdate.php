<?php
namespace Dhtmlx\Connector\Data;

class JSONDataItemUpdate extends DataItemUpdate {

    public function to_xml() {
        return array(
            "status" => $this->data["action_table_type"],
            "id" => $this->data["dataId"],
            "parent" => $this->get_parent_id(),
            "data" => $this->child->to_xml()
        );
    }

}