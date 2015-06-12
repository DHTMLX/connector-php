<?php
namespace Dhtmlx\Connector\DataStorage;
use Cake\ORM\TableRegistry;
use \Exception;

class PHPCakeDBDataWrapper extends ArrayDBDataWrapper {

    public function select($sql) {
        if(is_array($this->connection))	//result of findAll
            $query = $this->connection;
        else
            $query = $this->connection->find("all");

        $temp = array();
        foreach($query as $row)
            $temp[] = $row->toArray();

        return new ArrayQueryWrapper($temp);
    }

    protected function getErrorMessage() {
        $errors = $this->connection->invalidFields();
        $text = array();
        foreach ($errors as $key => $value){
            $text[] = $key." - ".$value[0];
        }
        return implode("\n", $text);
    }

    public function insert($data, $source) {
        $table = TableRegistry::get($source->get_source());
        $obj = $table->newEntity();
        $obj = $this->fillModel($obj, $data);
        $savedResult = $this->connection->save($obj);
        $data->success($savedResult->get($this->config->id["db_name"]));
    }

    public function delete($data, $source) {
        $table = TableRegistry::get($source->get_source());
        $obj = $table->get($data->get_id());
        $this->connection->delete($obj);
    }

    public function update($data, $source) {
        $table = TableRegistry::get($source->get_source());
        $obj = $table->get($data->get_id());
        $obj = $this->fillModel($obj, $data);
        $table->save($obj);
    }

    private function fillModel($obj, $data) {
        //Map data to model object.
        for($i = 0; $i < count($this->config->text); $i++) {
            $step=$this->config->text[$i];
            $obj->set($step["name"], $data->get_value($step["name"]));
        }

        if($relation = $this->config->relation_id["db_name"])
            $obj->set($relation, $data->get_value($relation));

        return $obj;
    }

    public function escape($str){
        throw new Exception("Not implemented");
    }

    public function query($str){
        throw new Exception("Not implemented");
    }

    public function get_new_id(){
        throw new Exception("Not implemented");
    }
}

?>