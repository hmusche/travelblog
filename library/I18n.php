<?php

namespace TravelBlog;

class I18n {
    static private $_instance = null;

    protected $_locale;

    private function __construct() {
        $this->_locale = \Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        date_default_timezone_set('Europe/Berlin');
    }

    static public function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getLocale() {
        return $this->_locale;
    }
}
