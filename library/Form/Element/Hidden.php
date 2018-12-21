<?php

namespace TravelBlog\Form\Element;

use TravelBlog\Form\ElementAbstract;

class Hidden extends ElementAbstract {
    protected $_template = 'hidden';

    public function populate(Array $data) {
        $this->_value = array_key_exists($this->_name, $data) ? $data[$this->_name] : null;
    }
}
