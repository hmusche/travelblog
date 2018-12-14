<?php

namespace TravelBlog;

class View {
    static private $_instance = null;

    protected $_template;
    protected $_data = [];

    private function __construct() {}

    public function setTemplate($template) {
        $this->_template = $template;
    }

    public function setData(Array $values) {
        foreach ($values as $key => $value) {
            $this->_data[$key] = $value;
        }
    }

    public function render($template = null) {
        if ($template === null) {
            $template = 'template/main.phtml';
        } else {
            $template = 'template/' . $template;
        }

        if (!file_exists($template)) {
            throw new \Exception('Template file ' . $template . ' not found.');
        }

        ob_start();

        require_once $template;

        return ob_get_clean();
    }

    static public function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}
