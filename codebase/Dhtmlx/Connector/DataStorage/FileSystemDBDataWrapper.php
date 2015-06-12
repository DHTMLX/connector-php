<?php
namespace Dhtmlx\Connector\DataStorage;
use Dhtmlx\Connector\DataStorage\TypeHandler\FileSystemTypeHandler;
use Dhtmlx\Connector\DataStorage\ResultHandler\FileSystemResultHandler;
use Dhtmlx\Connector\Tools\LogMaster;

/*! Most execution time is a standart functions for workin with FileSystem: is_dir(), dir(), readdir(), stat()
**/
class FileSystemDBDataWrapper extends DBDataWrapper {

    // returns list of files and directories
    public function select($source) {
        $relation = $this->getFileName($source->get_relation());
        // for tree checks relation id and forms absolute path
        if ($relation == '0') {
            $relation = '';
        } else {
            $path = $source->get_source();
        }
        $path = $source->get_source();
        $path = $this->getFileName($path);
        $path = realpath($path);
        if ($path == false) {
            return new FileSystemResultHandler();
        }

        if (strpos(realpath($path.'/'.$relation), $path) !== 0) {
            return new FileSystemResultHandler();
        }
        // gets files and directories list
        $res = $this->getFilesList($path, $relation);
        // sorts list
        $res = $res->sort($source->get_sort_by(), $this->config->data);
        return $res;
    }

    // gets files and directory list
    private function getFilesList($path, $relation) {
        $typeHandlerObj = FileSystemTypeHandler::getInstance();
        LogMaster::log("Query filesystem: ".$path);
        $dir = opendir($path.'/'.$relation);
        $result = new FileSystemResultHandler();
        // forms fields list
        for ($i = 0; $i < count($this->config->data); $i++) {
            $fields[] = $this->config->data[$i]['db_name'];
        }
        // for every file and directory of folder
        while ($file = readdir($dir)) {
            // . and .. should not be in output list
            if (($file == '.')||($file == '..')) {
                continue;
            }
            $newFile = array();
            // parse file name as Array('name', 'ext', 'is_dir')
            $fileNameExt = $this->parseFileName($path.'/'.$relation, $file);
            // checks if file should be in output array
            if (!$typeHandlerObj->checkFile($file, $fileNameExt)) {
                continue;
            }
            // takes file stat if it's need
            if ((in_array('size', $fields))||(in_array('date', $fields))) {
                $fileInfo = stat($path.'/'.$file);
            }

            // for every field forms list of fields
            for ($i = 0; $i < count($fields); $i++) {
                $field = $fields[$i];
                switch ($field) {
                    case 'filename':
                        $newFile['filename'] = $file;
                        break;
                    case 'full_filename':
                        $newFile['full_filename'] = $path."/".$file;
                        break;
                    case 'size':
                        $newFile['size'] = $fileInfo['size'];
                        break;
                    case 'extention':
                        $newFile['extention'] = $fileNameExt['ext'];
                        break;
                    case 'name':
                        $newFile['name'] = $fileNameExt['name'];
                        break;
                    case 'date':
                        $newFile['date'] = date("Y-m-d H:i:s", $fileInfo['ctime']);
                        break;
                }
                $newFile['relation_id'] = $relation.'/'.$file;
                $newFile['safe_name'] = $this->setFileName($relation.'/'.$file);
                $newFile['is_folder'] = $fileNameExt['is_dir'];
            }
            // add file in output list
            $result->addFile($newFile);
        }
        return $result;
    }

    // replaces '.' and '_' in id
    private function setFileName($filename) {
        $filename = str_replace(".", "{-dot-}", $filename);
        $filename = str_replace("_", "{-nizh-}", $filename);
        return $filename;
    }

    // replaces '{-dot-}' and '{-nizh-}' in id
    private function getFileName($filename) {
        $filename =  str_replace("{-dot-}", ".", $filename);
        $filename = str_replace("{-nizh-}", "_", $filename);
        return $filename;
    }

    // parses file name and checks if is directory
    private function parseFileName($path, $file) {
        $result = Array();
        if (is_dir($path.'/'.$file)) {
            $result['name'] = $file;
            $result['ext'] = 'dir';
            $result['is_dir'] = 1;
        } else {
            $pos = strrpos($file, '.');
            $result['name'] = substr($file, 0, $pos);
            $result['ext'] = substr($file, $pos + 1);
            $result['is_dir'] = 0;
        }
        return $result;
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