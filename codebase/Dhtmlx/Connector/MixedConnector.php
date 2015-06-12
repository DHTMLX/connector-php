<?php
namespace Dhtmlx\Connector;

class MixedConnector extends Connector {

    protected $connectors = array();

    public function add($name, $conn) {
        $this->connectors[$name] = $conn;
    }

    public function render() {
        $result = "{";
        $parts = array();
        foreach($this->connectors as $name => $conn) {
            $conn->asString(true);
            $parts[] = "\"".$name."\":".($conn->render())."\n";
        }
        $result .= implode(",\n", $parts)."}";
        echo $result;
    }
}