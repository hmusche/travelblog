<?php

namespace TravelBlog\Controller;

use Solsken\Http;
use Solsken\Controller;
use Solsken\Form;
use Solsken\Util;
use Solsken\Table;

use TravelBlog\Model\User;
use TravelBlog\Model\Post;

class Admin extends Controller {
    public function preDispatch() {
        parent::preDispatch();

        if (!Util::isLoggedIn() && $this->_request->get('action') != 'login') {
            Http::redirect('admin/login');
        }

        if (Util::isLoggedIn()) {
            $this->_view->user = $_SESSION['user'];
        }
    }

    public function indexAction() {
        $postModel = new Post;
        $posts = $postModel->getPosts([
            'draft',
            'active',
            'archived'
        ], 20, 0, 'updated');

        $table = new Table();
        $table->addColumns([
            'title' => [],
            'updated' => [
                'formatters' => [
                    'date'
                ]
            ]
        ])->addAction('edit', [
            'href' => 'admin/post/id/{id}',
            'icon' => 'edit'
        ])->setData($posts);

        $this->_view->table = $table;

        $this->_view->posts = $posts;
    }

    public function postAction() {
        $id        = $this->_request->getParam('id');
        $postModel = new Post;

        if ($id) {
            $post = $postModel->getPost($id);

            if (!$post) {
                Http::redirect('admin/post/');
            }
        }

        $form = new Form('post', [$postModel, 'updatePost']);
        $form->addLoadCallback($id, [$postModel, 'getPost'])->addElements([
            [
                'name' => 'title',
            ], [
                'name' => 'text',
                'type' => 'textarea',
                'options' => [
                    'attributes' => [
                        'rows' => 10
                    ]
                ]
            ], [
                'name' => 'status',
                'type' => 'select',
                'options' => [
                    'values' => [$postModel, 'getEnumSelect']
                ]
            ]
        ]);

        $form->handle();

        $this->_view->form = $form;
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
