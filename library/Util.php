<?php

namespace TravelBlog;

class Util {
    static public function toCamelCase($string) {
        $string = str_replace(['_', '-'], ' ', $string);
        $string = ucwords($string);
        $string = str_replace(' ', '', $string);

        return lcfirst($string);
    }
}
