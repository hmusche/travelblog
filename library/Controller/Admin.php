<?php

namespace TravelBlog\Controller;

use TravelBlog\Controller;
use TravelBlog\Model\User;
use TravelBlog\Form;

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

            $user = new User;

            $form = new Form('set-password', [$user, 'setPassword']);
            $form->setRedirect('admin/')->addElements([
                [
                    'type' => 'text',
                    'name' => 'login',
                    'value' => $login
                ], [
                    'type' => 'password',
                    'name' => 'password'
                ], [
                    'type' => 'password',
                    'name' => 'password_repeat',
                    'options' => [
                        'validators' => [
                            'match' => 'password'
                        ]
                    ]
                ]
            ]);

            $form->handle();

            $this->_view->form = $form;

            //var_dump($form);
        }

        $this->_view->displayForm = $displayForm;
    }
}
