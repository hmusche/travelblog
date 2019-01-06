<?php

namespace TravelBlog\Controller;

use TravelBlog\Controller;

class Page extends Controller {
    public function impressumAction() {
        $this->_view->header = ['title' => 'impressum'];
        $this->_view->textBody = 'impressum.text';
    }

    public function postDispatch() {
        $this->_view->template = 'page/template.phtml';
        parent::postDispatch();
    }
}
