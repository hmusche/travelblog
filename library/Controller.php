<?php

namespace TravelBlog;

use TravelBlog\Request;
use TravelBlog\Util;
use TravelBlog\View;

/**
 * Controller class to control Action
 */
class Controller {
    protected $_request;
    protected $_view;

    /**
     * Constructor, get Request and View Instances
     */
    public function __construct() {
        $this->_request = Request::getInstance();
        $this->_view = View::getInstance();
    }

    /**
     * Static method to get Instance of COntroller class fitting to current request
     *
     * @return TravelBlog\Controller
     */
    static public function getController() {
        $request = Request::getInstance();

        $controller = 'TravelBlog\\Controller\\' . ucfirst(Util::toCamelCase($request->get('controller')));
        $controller = new $controller;

        return $controller;
    }

    /**
     * Get Action from request object and call preDispatch, Action, and postDispatch
     */
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

    /**
     * Build default template. Method can be overriden oder extended in sub controller classes
     */
    public function preDispatch() {
        $defaultTemplate = $this->_request->get('controller') . DIRECTORY_SEPARATOR . $this->_request->get('action') . '.phtml';

        $this->_view->template = $defaultTemplate;
    }

    /**
     * Render Template with all data. Can be overriden on sub controller classes
     */
    public function postDispatch() {
        echo $this->_view->render();
    }
}
