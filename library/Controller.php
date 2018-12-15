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

        $this->preDispatch();
        $this->$method();
        $this->postDispatch();
    }

    public function preDispatch() {
        $defaultTemplate = $this->_request->get('controller') . DIRECTORY_SEPARATOR . $this->_request->get('action') . '.phtml';

        $this->_view->template = $defaultTemplate;
    }

    public function postDispatch() {
        echo $this->_view->render();
    }
}
