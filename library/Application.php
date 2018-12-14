<?php

namespace TravelBlog;

use TravelBlog\Controller;
use TravelBlog\Registry;
use Medoo\Medoo;

class Application {
    protected $_controller;

    public function __construct() {
        $config = include('config/config.php');
        $credentials = include('config/credentials.php');

        $config = array_merge_recursive($config, $credentials);

        Registry::set('app.config', $config);
        Registry::set('app.db', new Medoo($config['db']));

        $this->_controller = Controller::getController();
    }

    public function run() {
        $this->_controller->dispatch();
    }
}
