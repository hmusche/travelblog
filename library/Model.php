<?php

namespace TravelBlog;

use TravelBlog\Registry;

class Model {
    protected $_name;
    protected $_db;

    public function __construct() {
        $this->_db = Registry::get('app.db');
    }

    public function select($join, $columns = null, $where = null) {
        return $this->_db->select($this->_name, $join, $columns, $where);
    }

    public function get($join, $columns = null, $where = null) {
        return $this->_db->get($this->_name, $join, $columns, $where);
    }

    public function update($data, $where) {
        return $this->_db->update($this->_name, $data, $where);
    }

}
