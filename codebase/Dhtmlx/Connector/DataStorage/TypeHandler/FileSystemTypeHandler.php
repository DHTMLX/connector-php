<?php
namespace Dhtmlx\Connector\DataStorage\TypeHandler;

// singleton class for setting file types filter
class FileSystemTypeHandler {

    static private $instance = NULL;
    private $extentions = Array();
    private $extentions_not = Array();
    private $all = true;
    private $patterns = Array();
    // predefined types
    private $types = Array(
        'image' => Array('jpg', 'jpeg', 'gif', 'png', 'tiff', 'bmp', 'psd', 'dir'),
        'document' => Array('txt', 'doc', 'docx', 'xls', 'xlsx', 'rtf', 'dir'),
        'web' => Array('php', 'html', 'htm', 'js', 'css', 'dir'),
        'audio' => Array('mp3', 'wav', 'ogg', 'dir'),
        'video' => Array('avi', 'mpg', 'mpeg', 'mp4', 'dir'),
        'only_dir' => Array('dir')
    );


    static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new FileSystemTypeHandler();
        }
        return self::$instance;
    }

    // sets array of extentions
    public function setExtentions($ext) {
        $this->all = false;
        $this->extentions = $ext;
    }

    // adds one extention in array
    public function addExtention($ext) {
        $this->all = false;
        $this->extentions[] = $ext;
    }


    // adds one extention which will not ouputed in array
    public function addExtentionNot($ext) {
        $this->extentions_not[] = $ext;
    }


    // returns array of extentions
    public function getExtentions() {
        return $this->extentions;
    }

    // adds regexp pattern
    public function addPattern($pattern) {
        $this->all = false;
        $this->patterns[] = $pattern;
    }

    // clear extentions array
    public function clearExtentions() {
        $this->all = true;
        $this->extentions = Array();
    }

    // clear regexp patterns array
    public function clearPatterns() {
        $this->all = true;
        $this->patterns = Array();
    }

    // clear all filters
    public function clearAll() {
        $this->clearExtentions();
        $this->clearPatterns();
    }

    // sets predefined type
    public function setType($type, $clear = false) {
        $this->all = false;
        if ($type == 'all') {
            $this->all = true;
            return true;
        }
        if (isset($this->types[$type])) {
            if ($clear) {
                $this->clearExtentions();
            }
            for ($i = 0; $i < count($this->types[$type]); $i++) {
                $this->extentions[] = $this->types[$type][$i];
            }
            return true;
        } else {
            return false;
        }
    }


    // check file under setted filter
    public function checkFile($filename, $fileNameExt) {
        if (in_array($fileNameExt['ext'], $this->extentions_not)) {
            return false;
        }
        if ($this->all) {
            return true;
        }

        if ((count($this->extentions) > 0)&&(!in_array($fileNameExt['ext'], $this->extentions))) {
            return false;
        }

        for ($i = 0; $i < count($this->patterns); $i++) {
            if (!preg_match($this->patterns[$i], $filename)) {
                return false;
            }
        }
        return true;
    }
}