<?php

namespace TravelBlog;

use TravelBlog\Model\PostMeta;
use Solsken\Cookie;

class Controller extends \Solsken\Controller {
    public function preDispatch() {
        parent::preDispatch();

        $metaModel = new PostMeta();

        $this->_view->blogTitle     = 'no fly zone';
        $this->_view->postCountries = $metaModel->getCountries();
        $this->_view->postTags      = $metaModel->getTags();

        $this->_view->acceptCookie = Cookie::get('accept');
    }

}
