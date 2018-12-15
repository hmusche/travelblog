<?php

namespace TravelBlog\Form;

use TravelBlog\Util;
use TravelBlog\View;

abstract class ElementAbstract {
    protected $_name;
    protected $_attributes = [];
    protected $_validators = [];
    protected $_errors     = [];
    protected $_value;

    abstract public function populate(Array $data);

    public function __construct($name, $config, $value = null) {
        $defaultConfig = [
            'attributes' => [
                'type' => 'text'
            ],
            'validators' => [
                'required' => []
            ]
        ];

        $config = array_merge_recursive($defaultConfig, $config);

        $this->_name = $name;
        $this->_value = $value;
        $this->_attributes = array_merge($config['attributes'], $this->_attributes);

        foreach ($config['validators'] as $validator => $validatorOptions) {
            $validatorClass = "\\TravelBlog\\Form\\Validator\\" . ucfirst(Util::toCamelCase($validator));
            $validatorHash = serialize($validatorOptions) . $validator;

            $this->_validators[$validatorHash] = new $validatorClass($validatorOptions);
        }
    }


    public function isValid() {
        $valid = true;

        foreach ($this->_validators as $name => $validator) {
            if (!$validator->isValid($this->_value)) {
                $this->_errors[] = $validator->getError();
                $valid = false;
            }
        }

        return $valid;
    }

    public function getErrors() {
        return $this->_errors;
    }

    public function hasErrors() {
        return $this->_errors !== [];
    }

    public function getName() {
        return $this->_name;
    }

    public function getValue() {
        return $this->_value;
    }

    public function getAttributes() {
        return $this->_attributes;
    }

    public function getAttributeString() {
        $parts = [];

        foreach ($this->_attributes as $key => $value) {
            $parts[] = "$key=\"$value\"";
        }

        return implode(" ", $parts);
    }

    public function __toString() {
        $view = View::getInstance();

        return $view->partial("partial/element/{$this->_template}.phtml", [
            'element' => $this
        ]);
    }
}
