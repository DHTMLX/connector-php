<?php
namespace Dhtmlx\Connector;

/*! Connector class for DataView
**/
class ChartConnector extends DataViewConnector {
    public function __construct($res,$type=false,$item_type=false,$data_type=false){
        parent::__construct($res,$type,$item_type,$data_type);
    }
}