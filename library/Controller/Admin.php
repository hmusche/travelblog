<?php

namespace TravelBlog\Controller;

use TravelBlog\Controller;
use TravelBlog\Model\User;

class Admin extends Controller {
    public function indexAction() {
        $user = new User;

        $user->login('mu', 'xxx');
    }

    public function setPasswordAction() {
        $login = $this->_request->getParam('login');

        $displayForm = false;

        if ($login) {
            $displayForm = true;
            $this->_view->login = $login;
        }

        $this->_view->displayForm = $displayForm;
    }
}
