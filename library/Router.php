<?php

namespace TravelBlog;

use TravelBlog\Request;

class Router {

    static public function getController() {
        $request = Request::getInstance();

        return $request->get('controller');
    }
}
