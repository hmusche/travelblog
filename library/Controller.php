<?php

namespace TravelBlog;

use TravelBlog\Request;
use TravelBlog\Util;
use TravelBlog\View;

class Controller {
    protected $_request;
    protected $_view;

    public function __construct() {
        $this->_request = Request::getInstance();
        $this->_view = View::getInstance();
    }

    static public function getController() {
        $request = Request::getInstance();

        $controller = 'TravelBlog\\Controller\\' . ucfirst(Util::toCamelCase($request->get('controller')));
        $controller = new $controller;

        return $controller;
    }

    public function dispatch() {
        $action = Util::toCamelCase($this->_request->get('action'));
        $method = $action . 'Action';

        if (!method_exists($this, $method)) {
            throw new \Exception("Unknown Action $action");
        }

        $defaultTemplate = $this->_request->get('controller') . DIRECTORY_SEPARATOR . $this->_request->get('action') . '.phtml';

        $this->_view->template = $defaultTemplate;

        $this->$method();

        echo $this->_view->render();
    }
}
