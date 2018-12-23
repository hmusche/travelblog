<?php

namespace TravelBlog\Table;

use TravelBlog\Util;

class Column {
    protected $_config = [
        'key'        => '',
        'label'      => '',
        'formatters' => []
    ];

    protected $_formatters = [];

    public function __construct(array $config = []) {
        $this->_config = array_merge($this->_config, $config);
    }

    public function getLabel() {
        return $this->_config['label'] ?: $this->_config['key'];
    }

    public function getValue($row) {
        $value = isset($row[$this->_config['key']]) ? $row[$this->_config['key']] : '-';

        foreach ($this->_config['formatters'] as $formatter => $formatterConfig) {
            if (is_string($formatterConfig)) {
                $formatter = $formatterConfig;
                $formatterConfig = [];
            }

            $hash = $formatter . serialize($formatterConfig);

            if (!isset($this->_formatters[$hash])) {
                $formatter = '\\TravelBlog\\Formatter\\' . ucfirst(Util::toCamelCase($formatter));

                $this->_formatters[$hash] = new $formatter($formatterConfig);
            }

            $value = $this->_formatters[$hash]->format($value);
        }

        return $value;
    }
}
