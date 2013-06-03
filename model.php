<?php
include_once('db_wrapper.php');
Class Model extends DbWrapper {

    public $obj;

    public function __construct() {
        print_r('In index construct<pre>');
//        parent::__construct();
        $this->obj = DbWrapper::getInstance();
    }

    public function getAllUsers($limit = 10) {
        return $this->obj->query("select * from users LIMIT $limit");
    }

    public function find($tableName, $type = 'all', $options = array()) {
        if ($type == 'all') {
            if (isset($options['fields']) && !empty($options['fields'])) {
               $this->obj->select($options['fields'])->from($tableName);
            } else {
                $this->obj->select($type)->from($tableName);
            }
        }
        if (isset($options['conditions']) && !empty($options['conditions'])) {
            $this->obj->where($options['conditions']);
        }

        if (isset($options['limit']) && !empty($options['limit'])) {
           $this->obj->limit($options['limit']);
        }

        if (isset($options['order']) && !empty($options['order'])) {
            $order = explode(',', $options['order']);
            $this->obj->orderBy($order[0], $order[1]);
        }

        if (isset($options['group']) && !empty($options['group'])) {
            $this->obj->groupBy($options['group']);
        }

        return $this->obj->get();
    }

    public function save($tableName, $setParameters) {
        return $this->obj->save($tableName, $setParameters);
    }

    public function update($tableName, $setParameters = array(), $conditions = array()) {
        return $this->obj->save($tableName, $setParameters, $conditions);
    }

    public function delete($tableName, $conditions = array()) {
        return $this->obj->delete($tableName, $conditions);
    }


}
