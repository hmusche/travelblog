<?php

namespace TravelBlog\Form\Validator;

use TravelBlog\Form\Validator;

class Required extends Validator {
    protected $_error = 'input.required';

    public function isValid($value) {
        return trim($value) !== '';
    }
}
