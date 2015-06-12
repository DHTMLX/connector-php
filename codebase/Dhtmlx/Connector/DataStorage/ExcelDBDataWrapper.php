<?php
namespace Dhtmlx\Connector\DataStorage;
use Dhtmlx\Connector\DataStorage\ResultHandler\ExcelResultHandler;

/*! Implementation of DataWrapper for Excel
**/
class ExcelDBDataWrapper extends DBDataWrapper {
    public $emptyLimit = 10;
    public function excel_data($points){
        $path = $this->connection;
        $excel = PHPExcel_IOFactory::createReaderForFile($path);
        $excel = $excel->load($path);
        $result = array();
        $excelWS = $excel->getActiveSheet();

        for ($i=0; $i < sizeof($points); $i++) {
            $c = array();
            preg_match("/^([a-zA-Z]+)(\d+)/", $points[$i], $c);
            if (count($c) > 0) {
                $col = PHPExcel_Cell::columnIndexFromString($c[1]) - 1;
                $cell = $excelWS->getCellByColumnAndRow($col, (int)$c[2]);
                $result[] = $cell->getValue();
            }
        }

        return $result;
    }
    public function select($source) {
        $path = $this->connection;
        $excel = PHPExcel_IOFactory::createReaderForFile($path);
        $excel->setReadDataOnly(false);
        $excel = $excel->load($path);
        $excRes = new ExcelResultHandler();
        $excelWS = $excel->getActiveSheet();
        $addFields = true;

        $coords = array();
        if ($source->get_source() == '*') {
            $coords['start_row'] = 0;
            $coords['end_row'] = false;
        } else {
            $c = array();
            preg_match("/^([a-zA-Z]+)(\d+)/", $source->get_source(), $c);
            if (count($c) > 0) {
                $coords['start_row'] = (int) $c[2];
            } else {
                $coords['start_row'] = 0;
            }
            $c = array();
            preg_match("/:(.+)(\d+)$/U", $source->get_source(), $c);
            if (count($c) > 0) {
                $coords['end_row'] = (int) $c[2];
            } else {
                $coords['end_row'] = false;
            }
        }

        $i = $coords['start_row'];
        $end = 0;
        while ((($coords['end_row'] == false)&&($end < $this->emptyLimit))||(($coords['end_row'] !== false)&&($i < $coords['end_row']))) {
            $r = Array();
            $emptyNum = 0;
            for ($j = 0; $j < count($this->config->text); $j++) {
                $col = PHPExcel_Cell::columnIndexFromString($this->config->text[$j]['name']) - 1;
                $cell = $excelWS->getCellByColumnAndRow($col, $i);
                if (PHPExcel_Shared_Date::isDateTime($cell)) {
                    $r[PHPExcel_Cell::stringFromColumnIndex($col)] = PHPExcel_Shared_Date::ExcelToPHP($cell->getValue());
                }  else if ($cell->getDataType() == 'f') {
                    $r[PHPExcel_Cell::stringFromColumnIndex($col)] = $cell->getCalculatedValue();
                } else {
                    $r[PHPExcel_Cell::stringFromColumnIndex($col)] = $cell->getValue();
                }
                if ($r[PHPExcel_Cell::stringFromColumnIndex($col)] == '') {
                    $emptyNum++;
                }
            }
            if ($emptyNum < count($this->config->text)) {
                $r['id'] = $i;
                $excRes->addRecord($r);
                $end = 0;
            } else {
                if (DHX_IGNORE_EMPTY_ROWS == false) {
                    $r['id'] = $i;
                    $excRes->addRecord($r);
                }
                $end++;
            }
            $i++;
        }
        return $excRes;
    }

    public function query($sql) {
    }

    public function get_new_id() {
    }

    public function escape($data) {
    }

    public function get_next($res) {
        return $res->next();
    }
}