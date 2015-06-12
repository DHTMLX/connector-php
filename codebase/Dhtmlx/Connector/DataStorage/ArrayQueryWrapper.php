<?php
namespace Dhtmlx\Connector\DataStorage;

class ArrayQueryWrapper {
    public function __construct($data){
        $this->data = $data;
        $this->index = 0;
    }
}