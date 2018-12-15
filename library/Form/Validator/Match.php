<?php

namespace TravelBlog\Form\Validator;

use TravelBlog\Form\Validator;
use TravelBlog\Request;

class Match extends Validator {
    protected $_error = 'not.matching';

    public function isValid($value) {
        $against = is_array($this->_options) ? $this->_options['against'] : $this->_options;
        $request = Request::getInstance();

        return trim($value) === trim($request->getParam($against));
    }
}
