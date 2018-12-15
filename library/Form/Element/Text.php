<?php

namespace TravelBlog\Form\Element;

use TravelBlog\Form\ElementAbstract;

class Text extends ElementAbstract {
    protected $_template = 'text';

    public function populate(Array $data) {
        $this->_value = array_key_exists($this->_name, $data) ? $data[$this->_name] : null;
    }
}
