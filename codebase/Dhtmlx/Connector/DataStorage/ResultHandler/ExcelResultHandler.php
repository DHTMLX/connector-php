<?php
namespace Dhtmlx\Connector\DataStorage\ResultHandler;

class ExcelResultHandler {
    private $rows;
    private $currentRecord = 0;

    // add record to output list
    public function addRecord($file) {
        $this->rows[] = $file;
    }


    // return next record
    public function next() {
        if ($this->currentRecord < count($this->rows)) {
            $row = $this->rows[$this->currentRecord];
            $this->currentRecord++;
            return $row;
        } else {
            return false;
        }
    }


    // sorts records under $sort array
    public function sort($sort, $data) {
        if (count($this->files) == 0) {
            return $this;
        }
        // defines fields list if it's need
        for ($i = 0; $i < count($sort); $i++) {
            $fieldname = $sort[$i]['name'];
            if (!isset($this->files[0][$fieldname])) {
                if (isset($data[$fieldname])) {
                    $fieldname = $data[$fieldname]['db_name'];
                    $sort[$i]['name'] = $fieldname;
                } else {
                    $fieldname = false;
                }
            }
        }

        // for every sorting field will sort
        for ($i = 0; $i < count($sort); $i++) {
            // if field, setted in sort parameter doesn't exist, continue
            if ($sort[$i]['name'] == false) {
                continue;
            }
            // sorting by current field
            $flag = true;
            while ($flag == true) {
                $flag = false;
                // checks if previous sorting fields are equal
                for ($j = 0; $j < count($this->files) - 1; $j++) {
                    $equal = true;
                    for ($k = 0; $k < $i; $k++) {
                        if ($this->files[$j][$sort[$k]['name']] != $this->files[$j + 1][$sort[$k]['name']]) {
                            $equal = false;
                        }
                    }
                    // compares two records in list under current sorting field and sorting direction
                    if (((($this->files[$j][$sort[$i]['name']] > $this->files[$j + 1][$sort[$i]['name']])&&($sort[$i]['direction'] == 'ASC'))||(($this->files[$j][$sort[$i]['name']] < $this->files[$j + 1][$sort[$i]['name']])&&($sort[$i]['direction'] == 'DESC')))&&($equal == true)) {
                        $c = $this->files[$j];
                        $this->files[$j] = $this->files[$j+1];
                        $this->files[$j+1] = $c;
                        $flag = true;
                    }
                }
            }
        }
        return $this;
    }

}