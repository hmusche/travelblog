<?php

namespace TravelBlog;

use TravelBlog\Registry;

/**
 * Base class for Database Models
 */
class Model {
    /**
     * Name of current DB table
     * @var String
     */
    protected $_name;

    /**
     * Instance of database connection
     * @var Medoo\Medoo
     */
    protected $_db;

    /**
     * Constructor, get DB instance
     */
    public function __construct() {
        $this->_db = Registry::get('app.db');
    }

    /**
     * Magic method to call methods in Medoo Database connection. Table is prepended to arguments
     *
     * @param  String $method
     * @param  Array $args
     * @return Mixed
     */
    public function __call($method, $args) {
        if (method_exists($this->_db, $method)) {
            $args = array_merge([$this->_name], $args);
            return call_user_func_array([$this->_db, $method], $args);
        } else {
            throw new \Exception('Unknown DB method ' . $method);
        }
    }
}
