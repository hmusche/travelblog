<?php

namespace TravelBlog;

/**
 * View class
 */
class View {
    /**
     * Instance of class
     * @var View
     */
    static private $_instance = null;

    /**
     * Current Template for View
     * @var String
     */
    protected $_template;

    /**
     * Data to be used in View
     * @var Array
     */
    protected $_data = [];

    /**
     * Private constructor
     */
    private function __construct() {}

    /**
     * Set Template for View
     * @param String $template
     */
    public function setTemplate($template) {
        $this->_template = $template;
    }

    /**
     * Set array of data directly to View
     * @param Array $values
     */
    public function setData(Array $values) {
        foreach ($values as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Render current template with current data
     * @param  String $template Optional, if null current set template is used
     * @return String           Rendered template
     */
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

    /**
     * Renders given template with subset of data
     * @param  String $template Template name to use
     * @param  Array  $data     Data for template
     * @return String           Rendered partial
     */
    public function partial($template, Array $data = null) {
        $partialView = clone self::$_instance;
        $partialView->clearData();
        $partialView->setData($data);

        return $partialView->render($template);
    }

    /**
     * Clear data
     */
    public function clearData() {
        $this->_data = [];
    }

    /**
     * Magic setter to set values in view
     * @param String $key
     * @param Mixed  $value
     */
    public function __set($key, $value) {
        $this->_data[$key] = $value;
    }

    /**
     * Magic method to return data from View obkect
     * @param  String $key
     * @return Mixed
     */
    public function __get($key) {
        return array_key_exists($key, $this->_data) ? $this->_data[$key] : null;
    }

    /**
     * Return Instance
     */
    static public function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}
