<?php

namespace TravelBlog;

use TravelBlog\Controller;
use TravelBlog\Registry;
use Medoo\Medoo;

/**
 * Base Application class to pull everything together, get Configuration, Controller, and dispatch the request
 */
class Application {
    protected $_controller;

    /**
     * Get Configuration and set in Registry, and create Controller class
     */
    public function __construct() {
        /**
         * Merge credentials.php in config dir for secrets not to be pushed in git
         */
        $credentials = include('config/credentials.php');
        $config      = include('config/config.php');
        $config      = array_merge_recursive($config, $credentials);

        Registry::set('app.config', $config);
        Registry::set('app.db', new Medoo($config['db']));

        session_start();

        $this->_controller = Controller::getController();
    }

    /**
     * Dispatch the controller
     */
    public function run() {
        $this->_controller->dispatch();
    }
}
