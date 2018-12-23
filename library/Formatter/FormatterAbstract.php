<?php

namespace TravelBlog\Formatter;

abstract class FormatterAbstract {
    protected $_config;

    public function __construct(array $config = []) {
        $this->_config = array_merge($this->_config, $config);
    }

    abstract public function format($value);
}
