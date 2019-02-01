<?php

namespace TravelBlog;

class Content {
    static public function parse($text) {
        $text = nl2br($text);

        return $text;
    }
}
