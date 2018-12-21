<?php

namespace TravelBlog\Controller;

use TravelBlog\Http;
use TravelBlog\Controller;
use TravelBlog\Model\User;
use TravelBlog\Form;
use TravelBlog\Util;

class Admin extends Controller {
    public function preDispatch() {
        parent::preDispatch();

        if (!Util::isLoggedIn() && $this->_request->get('action') != 'login') {
            Http::redirect('admin/login');
        }

        $this->_view->user = $_SESSION['user'];
    }

    public function indexAction() {
    }

    public function loginAction() {
        if (Util::isLoggedIn()) {
            Http::redirect('admin/');
        }

        $user = new User();
        $form = new Form('login', [$user, 'login']);
        $form->setRedirect('admin/')->addElements([
            [
                'name' => 'login',
            ],[
                'type' => 'password',
                'name' => 'password'
            ]
        ]);

        $form->handle();

        $this->_view->form = $form;
    }

    public function logoutAction() {
        session_destroy();

        Http::redirect('admin/login');
    }

    public function setPasswordAction() {
        $login = $this->_request->getParam('login');

        if ($login) {
            $user  = new User;
            $token = $user->get('token', ['login' => $login]);

            if ($token && $this->_request->getParam('token') == $token) {
                $this->_view->login = $login;

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
                    ], [
                        'type' => 'hidden',
                        'name' => 'token',
                        'value' => $token
                    ]
                ]);

                $form->handle();

                $this->_view->form = $form;
            }
        }
    }
}
