<?php

namespace TravelBlog;

use TravelBlog\Registry;

class Request {
    static private $_instance = null;
    static private $_request = [
        'path' => '',
        'host' => '',
        'controller' => 'main',
        'action' => 'index',
        'get' => [],
        'post' => [],
        'params' => [],
        'is_xhr' => false,
        'method' => ''
    ];

    protected function __construct() {
        $this->_parseRequest();
    }

    static public function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getParam($key, $default = null) {
        return (array_key_exists($key, self::$_request['params'])) ? self::$_request['params'][$key] : $default;
    }

    public function get($key) {
        if (!array_key_exists($key, self::$_request)) {
            throw new \Exception('$key not found in request');
        }

        return self::$_request[$key];
    }

    protected function _parseRequest() {
        $config = Registry::get('app.config');

        $parts = explode('/', str_replace($config['path'], '', $_SERVER['REQUEST_URI']));

        if (trim($parts[0]) !== '') {
            self::$_request['controller'] = $parts[0];
        }

        if (isset($parts[1]) && trim($parts[1]) !== '') {
            self::$_request['action'] = $parts[1];
        }

        self::$_request['get']  = $_GET;
        self::$_request['post'] = $_POST;
        self::$_request['params'] = array_merge($_GET, $_POST);

        if (count($parts) > 2) {
            $currKey = false;

            foreach (array_slice($parts, 2) as $part) {
                if ($currKey === false) {
                    $currKey = $part;
                } else {
                    self::$_request['params'][$currKey] = $part;
                    $currKey = false;
                }
            }
        }

        self::$_request['is_xhr'] = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        self::$_request['method'] = strtolower($_SERVER['REQUEST_METHOD']);
    }

}
