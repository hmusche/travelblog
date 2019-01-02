<?php

namespace TravelBlog;

class Controller extends \Solsken\Controller {
    public function preDispatch() {
        parent::preDispatch();

        $this->_view->blogTitle = 'no fly zone';
    }

}
