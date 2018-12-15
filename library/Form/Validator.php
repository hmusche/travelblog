<?php

namespace TravelBlog\Form;

class Validator {
    protected $_error;
    protected $_options;

    public function __construct($options) {
        $this->_options = $options;
    }

    public function isValid($value) {
        return true;
    }

    public function getError() {
        return $this->_error;
    }
}
