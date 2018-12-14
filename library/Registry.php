<?php

namespace TravelBlog;

class Registry {
    static protected $_data = [];

    static public function set($key, $value) {
        self::$_data[$key] = $value;
    }

    static public function get($key, $default = null) {
        if (array_key_exists($key, self::$_data)) {
            return self::$_data[$key];
        } else {
            return $default;
        }
    }
}
