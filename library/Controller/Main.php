<?php

namespace TravelBlog\Controller;

use TravelBlog\Controller;

class Main extends Controller {
    public function indexAction() {
        $this->_view->foo = 'bar';
    }
}
