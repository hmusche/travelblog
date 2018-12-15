<?php

namespace TravelBlog;

use TravelBlog\Registry;

class Http {
    static public function redirect($location, $status = 302) {
        if (!preg_match('#https?::#', $location)) {
            $config = Registry::get('app.config');

            $location = $config['host'] . $config['path'] . $location;
        }

        header('Location: ' . $location, true, $status);
    }
}
