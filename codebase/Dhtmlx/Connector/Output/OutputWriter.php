<?php
namespace Dhtmlx\Connector\Output;

class OutputWriter {
    private $start;
    private $end;
    private $type;

    public function __construct($start, $end = ""){
        $this->start = $start;
        $this->end = $end;
        $this->type = "xml";
    }
    public function add($add){
        $this->start.=$add;
    }
    public function reset(){
        $this->start="";
        $this->end="";
    }
    public function set_type($add){
        $this->type=$add;
    }
    public function output($name="", $inline=true, $encoding=""){
        ob_clean();

        if ($this->type == "xml"){
            $header = "Content-type: text/xml";
            if ("" != $encoding)
                $header.="; charset=".$encoding;
            header($header);
        }

        echo $this->__toString();
    }
    public function __toString(){
        return $this->start.$this->end;
    }
}