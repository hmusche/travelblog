<?php

namespace TravelBlog;

use TravelBlog\Form\ElementAbstract;
use TravelBlog\Form\Element\Submit;
use TravelBlog\Request;
use TravelBlog\View;
use TravelBlog\Http;

/**
 * Build and validate Forms
 */
class Form {
    /**
     * ID of current form
     * @var string
     */
    protected $_formId;

    /**
     * Callback to call after successful handling of form data
     * @var Callable
     */
    protected $_callback;

    protected $_loadCallback;

    protected $_id;

    /**
     * Array of Elements of current Form
     * @var Array
     */
    protected $_elements = [];

    /**
     * Aarray of occured errors
     * @var Array
     */
    protected $_errors = [];

    /**
     * Current posted data in form
     * @var Array
     */
    protected $_data = null;

    /**
     * URL to redirect to after successful handling
     * @var String
     */
    protected $_redirect;

    /**
     * Default options for added elements
     * @var Array
     */
    protected $_defaultElement = [
        'name'    => 'input',
        'type'    => 'text',
        'value'   => null,
        'options' => []
    ];

    /**
     * Constructor
     *
     * @param String   $formId    ID for current Form, used for validation of correct form posting
     * @param Callable $callback  Callable to be called after successful handling
     */
    public function __construct($formId, $callback) {
        $this->_formId   = $formId;
        $this->_callback = $callback;
    }

    /**
     * Handle Form. Sets all data, ensures validation, and calls callback
     *
     * @return Mixed if successfully handled data, return of callback, otherwise bool
     */
    public function handle() {
        $req = Request::getInstance();

        /**
         * If we don't do a post, then load data
         */
        if ($req->get('method') != 'post') {
            $this->_loadData();

            return true;
        }

        /**
         * Set Data to all elements
         */
        $this->setData();

        /**
         * Check posted form ID against current ID to handle multiple forms
         */
        if (!isset($this->_data['form_id']) || $this->_data['form_id'] != $this->_formId) {
            return false;
        }

        /**
         * Check validation, errors are available in $this->_errors
         */
        if (!$this->isValid()) {
            return false;
        }

        /**
         * Get Form data from all elements, so only data of consisting of defined elements is given to method
         * @todo Do (un)formatting of data in elements
         */
        $formData = [];

        foreach ($this->_elements as $element) {
            $formData[$element->getName()] = $element->getValue();
        }

        $where = [];

        if ($this->_id) {
            $where['id'] = $this->_id;
        }

        /**
         * Call callback and handle response. If XHR, just repond with json, otherwise do redirect
         */
        $return = $this->fireCallback($formData, $where);

        if ($req->get('is_xhr')) {
            echo json_encode([
                'status' => $this->_errors === [] && $return ? 'success' : 'error'
            ]);
        } else if ($return && $this->_errors === []) {
            Http::redirect($this->getRedirect($return));
        }

        return $this->_errors === [];
    }

    protected function _loadData() {
        if ($this->_loadCallback && $this->_id) {
            $data = call_user_func($this->_loadCallback, $this->_id);

            if ($data) {
                $this->setData($data);
            }
        }
    }

    /**
     * Set Data
     * @param Array $data  Optional, if null, we just get Params
     */
    public function setData(Array $data = null) {
        if (!$this->_data) {
            if ($data === null) {
                $req = Request::getInstance();
                $data = $req->get('params');
            }

            $this->_data = $data;

            foreach ($this->_elements as $element) {
                $element->populate($data);
            }
        }

        return $this;
    }

    /**
     * Check if form is currently valid
     *
     * @return boolean       True if all posted data is valid
     */
    public function isValid() {
        $isValid = true;

        foreach ($this->_elements as $element) {
            if (!$element->isValid()) {
                $this->_errors[$element->getName()] = $element->getErrors();
                $isValid = false;
            }
        }

        return $isValid;
    }

    /**
     * Fire Callback
     * @param  Array $data Data to pass to method
     * @return Mixed       Return of called method
     */
    public function fireCallback($data, $where = []) {
        $return = call_user_func($this->_callback, $data, $where);

        if (!$return) {
            $this->_errors['global'] = 'General error';
        }

        return $return;
    }

    public function addLoadCallback($id, $callback) {
        if ($id) {
            $this->_loadCallback = $callback;
            $this->_id = $id;
        }

        return $this;
    }

    /**
     * Set redirect for form
     * @param String $location URL to redirect, can be full URL or path component
     * @todo Allow placeholder for returned IDs and so on
     */
    public function setRedirect($location) {
        $this->_redirect = $location;

        return $this;
    }

    public function getRedirect($id = null) {
        if ($this->_redirect) {
            return $this->_redirect;
        }

        $req = Request::getInstance();

        $location = $req->get('controller') . '/' . $req->get('action');

        if ($id && is_numeric($id)) {
            $location .= '/id/' . $id;
        }

        return $location;
    }

    /**
     * Add single element to Form, see $_defaultElement for basic structure
     * @param array $element Element definition
     */
    public function addElement(array $element) {
        $element = array_merge($this->_defaultElement, $element);
        $class   = "\\TravelBlog\\Form\\Element\\" . ucfirst(Util::toCamelCase($element['type']));
        $name    = $element['name'];
        $obj     = new $class($name, $element['options'], $element['value']);

        $this->_elements[$name] = $obj;

        return $this;
    }

    /**
     * Add multiple elements
     * @param Array $elements Array of element definitions
     */
    public function addElements(Array $elements) {
        foreach ($elements as $element) {
            $this->addElement($element);
        }

        return $this;
    }

    /**
     * Check if Form has errors
     * @return boolean
     */
    public function hasErrors() {
        return $this->_errors !== [];
    }

    /**
     * Return Form errors
     * @return Array
     */
    public function getErrors() {
        return $this->_errors;
    }

    /**
     * Magic method to render form with all elements
     * @return string
     */
    public function __toString() {
        $view = View::getInstance();

        return $view->partial('partial/form.phtml', [
            'elements' => $this->_elements,
            'formId'   => $this->_formId,
            'errors'   => $this->getErrors(),
            'submit'   => new Submit('submit', [])
        ]);
    }
}
