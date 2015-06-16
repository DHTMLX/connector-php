<?php
namespace Dhtmlx\Connector;

class TreeConnector extends Connector {
    protected $parent_name = 'id';
    public $rootId = "0";

    /*! constructor

        Here initilization of all Masters occurs, execution timer initialized
        @param res
            db connection resource
        @param type
            string , which hold type of database ( MySQL or Postgre ), optional, instead of short DB name, full name of DataWrapper-based class can be provided
        @param item_type
            name of class, which will be used for item rendering, optional, DataItem will be used by default
        @param data_type
            name of class which will be used for dataprocessor calls handling, optional, DataProcessor class will be used by default.
     *	@param render_type
     *		name of class which will provides data rendering
    */
    public function __construct($res, $type = false, $item_type = false, $data_type = false, $render_type = false)
    {
        if (!$item_type) $item_type = "Dhtmlx\\Connector\\Data\\TreeDataItem";
        if (!$data_type) $data_type = "Dhtmlx\\Connector\\DataProcessor\\TreeDataProcessor";
        if (!$render_type) $render_type = "Dhtmlx\\Connector\\Output\\TreeRenderStrategy";
        parent::__construct($res, $type, $item_type, $data_type, $render_type);
    }

    //parse GET scoope, all operations with incoming request must be done here
    public function parse_request()
    {
        parent::parse_request();

        if (isset($_GET[$this->parent_name]))
            $this->request->set_relation($_GET[$this->parent_name]);
        else
            $this->request->set_relation($this->rootId);

        $this->request->set_limit(0, 0); //netralize default reaction on dyn. loading mode
    }

    /*! renders self as  xml, starting part
     */
    public function xml_start()
    {
        $attributes = "";
        foreach ($this->attributes as $k => $v)
            $attributes .= " " . $k . "='" . $v . "'";

        return "<tree id='" . $this->request->get_relation() . "'" . $attributes . ">";
    }

    /*! renders self as  xml, ending part
    */
    public function xml_end()
    {
        $this->fill_collections();
        return $this->extra_output . "</tree>";
    }
}
