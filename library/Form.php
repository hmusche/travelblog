<?php

namespace TravelBlog;

use TravelBlog\Form\ElementAbstract;
use TravelBlog\Form\Element\Submit;
use TravelBlog\Request;
use TravelBlog\View;
use TravelBlog\Http;

class Form {
    protected $_formId;
    protected $_callback;
    protected $_elements = [];
    protected $_errors = [];
    protected $_data = null;
    protected $_redirect;

    protected $_defaultElement = [
        'name'    => 'input',
        'type'    => 'text',
        'value'   => null,
        'options' => [

        ]
    ];

    public function __construct($formId, $callback) {
        $this->_formId   = $formId;
        $this->_callback = $callback;
    }

    public function handle() {
        $req = Request::getInstance();

        if ($req->get('method') != 'post') {
            return true;
        }

        $this->setData();

        if (!isset($this->_data['form_id']) || $this->_data['form_id'] != $this->_formId) {
            return false;
        }

        if (!$this->isValid()) {
            return false;
        }

        $formData = [];

        foreach ($this->_elements as $element) {
            $formData[$element->getName()] = $element->getValue();
        }

        $return = $this->fireCallback($formData);

        if ($req->get['is_xhr']) {
            echo json_encode([
                'status' => $this->_errors === [] && $return ? 'success' : 'error'
            ]);
        } else if ($return && $this->_errors === []) {
            Http::redirect($this->_redirect);
        }

        return $this->_errors === [];
    }

    public function setData($data = null) {
        if (!$this->_data) {
            if ($data === null) {
                $req = Request::getInstance();
                $data = $req->get('post');
            }

            $this->_data = $data;

            foreach ($this->_elements as $element) {
                $element->populate($data);
            }
        }

        return $this;
    }

    public function isValid($data = null) {
        $isValid = true;

        foreach ($this->_elements as $element) {
            if (!$element->isValid()) {
                $this->_errors[$element->getName()] = $element->getErrors();
                $isValid = false;
            }
        }

        return $isValid;
    }

    public function fireCallback($data) {
        $req = Request::getInstance();

        $return = call_user_func($this->_callback, $data);

        if (!$return) {
            $this->_errors['global'] = 'General error';
        }

        return $return;
    }

    public function setRedirect($location) {
        $this->_redirect = $location;

        return $this;
    }

    public function addElement(array $element) {
        $element = array_merge($this->_defaultElement, $element);
        $class   = "\\TravelBlog\\Form\\Element\\" . ucfirst(Util::toCamelCase($element['type']));
        $name    = $element['name'];
        $obj     = new $class($name, $element['options'], $element['value']);

        $this->_elements[$name] = $obj;

        return $this;
    }

    public function addElements(Array $elements) {
        foreach ($elements as $element) {
            $this->addElement($element);
        }

        return $this;
    }

    public function hasErrors() {
        return $this->_errors !== [];
    }

    public function getErrors() {
        return $this->_errors;
    }

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
