<?php

namespace TravelBlog;

use TravelBlog\Model\PostMeta;

class Controller extends \Solsken\Controller {
    public function preDispatch() {
        parent::preDispatch();

        $metaModel = new PostMeta();
        $countries = $metaModel->getCountries();

        $this->_view->blogTitle = 'no fly zone';
        $this->_view->postCountries = $countries;
    }

}
