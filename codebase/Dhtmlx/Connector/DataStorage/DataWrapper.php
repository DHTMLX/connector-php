<?php
namespace Dhtmlx\Connector\DataStorage;
use \Exception;

/*! Base abstraction class, used for data operations
	Class abstract access to data, it is a base class to all DB wrappers
**/
abstract class DataWrapper {
    protected $connection;
    protected $config;//!< DataConfig instance
    /*! constructor
        @param connection
            DB connection
        @param config
            DataConfig instance
    */
    public function __construct($connection = false,$config = false){
        $this->config=$config;
        $this->connection=$connection;
    }

    /*! insert record in storage

        @param data
            DataAction object
        @param source
            DataRequestConfig object
    */
    abstract function insert($data,$source);

    /*! delete record from storage

        @param data
            DataAction object
        @param source
            DataRequestConfig object
    */
    abstract function delete($data,$source);

    /*! update record in storage

        @param data
            DataAction object
        @param source
            DataRequestConfig object
    */
    abstract function update($data,$source);

    /*! select record from storage

        @param source
            DataRequestConfig object
    */
    abstract function select($source);

    /*! get size of storage

        @param source
            DataRequestConfig object
    */
    abstract function get_size($source);

    /*! get all variations of field in storage

        @param name
            name of field
        @param source
            DataRequestConfig object
    */
    abstract function get_variants($name,$source);

    /*! checks if there is a custom sql string for specified db operation

        @param  name
            name of DB operation
        @param  data
            hash of data
        @return
            sql string
    */
    public function get_sql($name,$data){
        return ""; //custom sql not supported by default
    }

    /*! begins DB transaction
    */
    public function begin_transaction(){
        throw new Exception("Data wrapper not supports transactions.");
    }
    /*! commits DB transaction
    */
    public function commit_transaction(){
        throw new Exception("Data wrapper not supports transactions.");
    }
    /*! rollbacks DB transaction
    */
    public function rollback_transaction(){
        throw new Exception("Data wrapper not supports transactions.");
    }
}

