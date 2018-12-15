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
            $this->$key = $value;
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

        require $template;

        return ob_get_clean();
    }

    public function partial($template, Array $data = null) {
        $partialView = clone self::$_instance;
        $partialView->clearData();
        $partialView->setData($data);

        return $partialView->render($template);
    }

    public function clearData() {
        $this->_data = [];
    }

    public function __set($key, $value) {
        $this->_data[$key] = $value;
    }

    public function __get($key) {
        return array_key_exists($key, $this->_data) ? $this->_data[$key] : null;
    }

    static public function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}
