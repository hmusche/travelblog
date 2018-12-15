<?php

namespace TravelBlog;

use TravelBlog\Registry;

/**
 * Helper class for all HTTP needs
 */
class Http {
    /**
     * Redirect to given $location.
     * @var String  $location    Can be full URL or path part
     * @var Integer $status      Returncode for Redirect, default = 302
     */
    static public function redirect($location, $status = 302) {
        if (!preg_match('#https?::#', $location)) {
            $config = Registry::get('app.config');

            $location = $config['host'] . $config['path'] . $location;
        }

        header('Location: ' . $location, true, $status);
        exit;
    }
}
