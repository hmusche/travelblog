<?php

namespace TravelBlog\Controller;

use Solsken\Controller;

class Main extends Controller {
    public function indexAction() {
        $this->_view->foo = 'bar';
    }
}
