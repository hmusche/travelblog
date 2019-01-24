<?php

namespace TravelBlog\Controller;

use TravelBlog\Controller;

class Page extends Controller {
    public function impressumAction() {
        $this->_view->header = [
            'title' => 'impressum',
            'image' => $this->_view->webhost . 'asset/img/impressum.jpg'
        ];
        $this->_view->textBody = 'impressum.text';
    }

    public function dprAction() {
        $this->_view->header = [
            'title' => 'data.protection',
            'image' => $this->_view->webhost . 'asset/img/impressum.jpg'
        ];
        $this->_view->textBody = 'data.protection.text';
    }


    public function aboutusAction() {
        $this->_view->header = [
            'title' => 'about.us',
            'image' => $this->_view->webhost . 'asset/img/aboutus.jpg'
        ];
        $this->_view->textBody = 'about.us.text';
    }

    public function postDispatch() {
        $this->_view->template = 'page/template.phtml';
        parent::postDispatch();
    }
}
