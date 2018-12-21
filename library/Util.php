<?php

namespace TravelBlog;

/**
 * Helper Class
 */
class Util {
    /**
     * Transform string to camelCase, first letter is lowercase
     * @var     String
     * @return  String
     */
    static public function toCamelCase($string) {
        $string = str_replace(['_', '-'], ' ', $string);
        $string = ucwords($string);
        $string = str_replace(' ', '', $string);

        return lcfirst($string);
    }

    static public function isLoggedIn() {
        return isset($_SESSION['user']);
    }
}
