<?php

namespace TravelBlog\Formatter;

use TravelBlog\I18n;

class Date extends FormatterAbstract {
    protected $_config = [
        'date' => 'short',
        'time' => 'short'
    ];

    public function format($value) {
        $i18n = I18n::getInstance();

        $dateFmt = null;
        $timeFmt = null;

        foreach (['date', 'time'] as $key) {
            $val = strtoupper($this->_config[$key]);
            $var = "{$key}Fmt";
            $$var = constant("\IntlDateFormatter::$val");
        }

        $formatter = new \IntlDateFormatter($i18n->getLocale(), $dateFmt, $timeFmt);

        return $formatter->format($value);
    }
}
