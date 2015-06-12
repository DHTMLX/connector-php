<?php
namespace Dhtmlx\Connector\Tools;
use \Exception;

/*! Class which allows to assign|fire events.
*/
class EventMaster {
    private $events;//!< hash of event handlers
    private $master;
    private static $eventsStatic=array();

    /*! constructor
    */
    function __construct(){
        $this->events=array();
        $this->master = false;
    }
    /*! Method check if event with such name already exists.
        @param name
            name of event, case non-sensitive
        @return
            true if event with such name registered, false otherwise
    */
    public function exist($name){
        $name=strtolower($name);
        return (isset($this->events[$name]) && sizeof($this->events[$name]));
    }
    /*! Attach custom code to event.

        Only on event handler can be attached in the same time. If new event handler attached - old will be detached.

        @param name
            name of event, case non-sensitive
        @param method
            function which will be attached. You can use array(class, method) if you want to attach the method of the class.
    */
    public function attach($name,$method=false){
        //use class for event handling
        if ($method === false){
            $this->master = $name;
            return;
        }
        //use separate functions
        $name=strtolower($name);
        if (!array_key_exists($name,$this->events))
            $this->events[$name]=array();
        $this->events[$name][]=$method;
    }

    public static function attach_static($name, $method){
        $name=strtolower($name);
        if (!array_key_exists($name,EventMaster::$eventsStatic))
            EventMaster::$eventsStatic[$name]=array();
        EventMaster::$eventsStatic[$name][]=$method;
    }

    public static function trigger_static($name, $method){
        $arg_list = func_get_args();
        $name=strtolower(array_shift($arg_list));

        if (isset(EventMaster::$eventsStatic[$name]))
            foreach(EventMaster::$eventsStatic[$name] as $method){
                if (is_array($method) && !method_exists($method[0],$method[1]))
                    throw new Exception("Incorrect method assigned to event: ".$method[0].":".$method[1]);
                if (!is_array($method) && !function_exists($method))
                    throw new Exception("Incorrect function assigned to event: ".$method);
                call_user_func_array($method, $arg_list);
            }
        return true;
    }

    /*! Detach code from event
        @param	name
            name of event, case non-sensitive
    */
    public function detach($name){
        $name=strtolower($name);
        unset($this->events[$name]);
    }
    /*! Trigger event.
        @param	name
            name of event, case non-sensitive
        @param data
            value which will be provided as argument for event function,
            you can provide multiple data arguments, method accepts variable number of parameters
        @return
            true if event handler was not assigned , result of event hangler otherwise
    */
    public function trigger($name,$data){
        $arg_list = func_get_args();
        $name=strtolower(array_shift($arg_list));

        if (isset($this->events[$name]))
            foreach($this->events[$name] as $method){
                if (is_array($method) && !method_exists($method[0],$method[1]))
                    throw new Exception("Incorrect method assigned to event: ".$method[0].":".$method[1]);
                if (!is_array($method) && !function_exists($method))
                    throw new Exception("Incorrect function assigned to event: ".$method);
                call_user_func_array($method, $arg_list);
            }

        if ($this->master !== false)
            if (method_exists($this->master, $name))
                call_user_func_array(array($this->master, $name), $arg_list);

        return true;
    }
}