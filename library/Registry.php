<?php

namespace TravelBlog;

/**
 * Static registry class for global data
 */
class Registry {
    static protected $_data = [];

    /**
     * Set value
     * @var String $key
     * @var Mixed  $value
     */
    static public function set($key, $value) {
        self::$_data[$key] = $value;
    }

    /**
     * Get Value from Registry, returns null if not set
     * @var String $key         Key to retrieve
     * @var Mixed  $default     Default value to return if not found, defaults to NULL
     */
    static public function get($key, $default = null) {
        if (array_key_exists($key, self::$_data)) {
            return self::$_data[$key];
        } else {
            return $default;
        }
    }
}
