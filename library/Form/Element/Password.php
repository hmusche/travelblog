<?php

namespace TravelBlog\Form\Element;

use TravelBlog\Form\Element\Text;

class Password extends Text {
    protected $_template = 'text';

    protected $_attributes = [
        'type' => 'password'
    ];

    public function populate(Array $data) {
        $this->_value = array_key_exists($this->_name, $data) ? $data[$this->_name] : null;
    }
}
