<?php

namespace TravelBlog\Controller;

use Solsken\Http;
use Solsken\Controller;
use Solsken\Form;
use Solsken\Util;
use Solsken\Table;
use Solsken\Registry;
use Solsken\I18n;
use Solsken\Cookie;

use TravelBlog\Model\User;
use TravelBlog\Model\Translation;
use TravelBlog\Model\Post;
use TravelBlog\Model\Stat;
use TravelBlog\Model\PostMedia;

class Admin extends Controller {
    public function preDispatch() {
        session_start();
        parent::preDispatch();

        if (!Util::isLoggedIn() && $this->_request->get('action') != 'login') {
            Http::redirect('admin/login');
        }

        if (Util::isLoggedIn()) {
            $this->_view->user = $_SESSION['user'];
        }

        $this->_view->includeMapGl = true;
        $this->_view->acceptCookie = Cookie::get('accept');
    }

    public function indexAction() {
        $postModel = new Post;
        $posts = $postModel->getPosts([
            'status' => [
                'draft',
                'active',
                'archived'
            ]
        ], 20, 0, 'updated');

        $table = new Table();
        $table->addColumns([
            'heading' => [],
            'updated' => [
                'formatters' => [
                    'date'
                ]
            ],
            'posted' => [
                'formatters' => [
                    'date'
                ]
            ],
            'status' => [
                'formatters' => [
                    'translate'
                ]
            ]
        ])->addAction('edit', [
            'href' => 'admin/post/id/{id}',
            'icon' => 'edit',
            'row'  => true
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
        $form->addLoadCallback($id, [$postModel, 'getPost'])->addGroups([
            [
                'name' => 'data',
                'class' => 'col-xs-12 col-md-4'
            ], [
                'name' => 'text',
                'class' => 'col-xs-12 col-md-8'
            ], [
                'name' => 'geo',
                'class' => 'col-xs-12 col-md-6'
            ], [
                'name' => 'media',
                'class' => 'col-xs-12 col-md-6'
            ]
        ])->addElements([
            [
                'name' => 'title',
                'group' => 'data',
                'options' => [
                    'validators' => [
                        'required' => false
                    ]
                ]
            ], [
                'name' => 'subtitle',
                'group' => 'data',
                'options' => [
                    'validators' => [
                        'required' => false
                    ]
                ]
            ], [
                'name' => 'status',
                'group' => 'data',
                'type' => 'select',
                'options' => [
                    'values' => [$postModel, 'getEnumSelect'],
                    'validators' => [
                        'required' => false
                    ]
                ]
            ], [
                'name' => 'tag',
                'group' => 'data',
                'options' => [
                    'validators' => [
                        'required' => false
                    ]
                ]
            ], [
                'name' => 'posted',
                'group' => 'data',
                'type' => 'date',
                'options' => [
                    'validators' => [
                        'required' => false
                    ]
                ]
            ], [
                'name' => 'text',
                'group' => 'text',
                'type' => 'textarea',
                'options' => [
                    'attributes' => [
                        'rows' => 10
                    ],
                    'validators' => [
                        'required' => false
                    ]
                ]
            ], [
                'name' => 'longitude',
                'group' => 'geo',
                'options' => [
                    'attributes' => [
                        'data-geo' => 'longitude'
                    ],
                    'validators' => [
                        'required' => false
                    ]
                ]
            ], [
                'name' => 'latitude',
                'group' => 'geo',
                'options' => [
                    'attributes' => [
                        'data-geo' => 'latitude'
                    ],
                    'validators' => [
                        'required' => false
                    ]
                ]
            ], [
                'name' => 'files',
                'group' => 'media',
                'type' => 'file',
                'options' => [
                    'label' => 'photos',
                    'preview' => 'post-media',
                    'validators' => [
                        'required' => false
                    ],
                    'attributes' => [
                        'multiple' => 'multiple'
                    ]
                ]
            ]
        ]);

        $form->handle();

        $this->_view->form = $form;
    }

    public function deletePostMediaAction() {
        $postId = $this->_request->getParam('post_id');
        $file   = $this->_request->getParam('file');
        $status = 'failed';

        if ($postId && $file) {
            $pmModel = new PostMedia();
            if ($pmModel->deleteMedia($postId, $file)) {
                $status = 'success';
            }

        }

        echo json_encode(['status' => $status]);
        exit;
    }

    public function postMediaMetaAction() {
        $postId = $this->_request->getParam('post_id');
        $file   = $this->_request->getParam('file');
        $sort   = $this->_request->getParam('sort', 0);
        $subtitle = $this->_request->getParam('subtitle', '');
        $status = 'failed';

        if ($postId && $file) {
            $pmModel = new PostMedia();
            if ($pmModel->update(['sort' => $sort, 'subtitle' => $subtitle], ['post_id' => $postId, 'filename' => $file])) {
                $status = 'success';
            }

        }

        echo json_encode(['status' => $status]);
        exit;

    }

    public function statListAction() {
        $statModel = new Stat();


        $table = new Table();
        $table->addColumns([
            'id' => [],
            'key' => [
                'formatters' => [
                    'translate'
                ]
            ],
            'value' => []
        ])->addAction('edit', [
            'href' => 'admin/stat/id/{id}',
            'icon' => 'edit'
        ])->addAction('add', [
            'href' => 'admin/stat-up/id/{id}',
            'icon' => 'plus-square'
        ])->addAction('sortup', [
            'href' => 'admin/stat-sort/id/{id}/way/up',
            'icon' => 'arrow-alt-circle-up'
        ])->addAction('sortdown', [
            'href' => 'admin/stat-sort/id/{id}/way/down',
            'icon' => 'arrow-alt-circle-down'
        ])->setData($statModel->getStats());

        $this->_view->table = $table;
    }

    public function statUpAction() {
        $statModel = new Stat();
        $id        = $this->_request->getParam('id');

        $statModel->update(['value[+]' => 1], ['id' => $id]);

        Http::redirect('admin/stat-list');
    }

    public function statSortAction() {
        $statModel = new Stat();
        $id        = $this->_request->getParam('id');
        $way       = $this->_request->getParam('way');
        $maxSort   = $statModel->max('sort') ?: 0;

        $stat = $statModel->getStat($id);

        if ($way == 'up' && $stat['sort'] > 1) {
            $statModel->update(['sort[+]' => 1], ['sort' => $stat['sort'] - 1]);
            $statModel->update(['sort[-]' => 1], ['id' => $id]);
        } else if ($way == 'down' && ($stat['sort'] + 1) < $maxSort){
            $statModel->update(['sort[1]' => 1], ['sort' => $stat['sort'] + 1]);
            $statModel->update(['sort[+]' => 1], ['id' => $id]);
        }

        Http::redirect('admin/stat-list');
    }

    public function statAction() {
        $statModel = new Stat();
        $i18n      = I18n::getInstance();
        $id        = $this->_request->getParam('id');

        $form = new Form('stat', [$statModel, 'updateStat']);
        $form->addLoadCallback($id, [$statModel, 'getStat']);
        $form->addGroup([
            'name' => 'title',
            'class' => 'col-xs-12 col-md-4'
        ]);

        $form->addGroup([
            'name' => 'value',
            'class' => 'col-xs-12 col-md-4'
        ]);

        foreach ($i18n->getSupportedLocales() as $locale) {
            $locale = substr($locale, 0, 2);
            $form->addElement([
                'name' => 'translation_' . $locale,
                'label' => 'stat.' . $locale,
                'group' => 'title',
            ]);
        }

        $form->addElement([
            'name' => 'value',
            'group' => 'value'
        ]);

        $form->handle();

        $this->_view->form = $form;
    }

    public function translationAction() {
        $tModel           = new Translation();
        $supportedLocales = I18n::getInstance()->getSupportedLocales();
        $key              = $this->_request->getParam('key');

        $form = new Form('post', [$tModel, 'updateTranslations']);
        $form->setRedirect('admin/translation-list')->addLoadCallback($key, [$tModel, 'getKey']);
        $form->addElement([
            'name' => 'key'
        ]);

        foreach ($supportedLocales as $locale) {
            $locale = substr($locale, 0, 2);
            $form->addElement([
                'name' => 'translation_' . $locale,
                'type' => 'textarea',
                'options' => [
                    'validators' => [
                        'required' => false
                    ]
                ]
            ]);
        }

        $form->handle();

        $this->_view->form = $form;

    }

    public function translationListAction() {
        $config = Registry::get('app.config');
        $tModel = new Translation();

        $missing = $tModel->getMissing();

        $table = new Table();
        $table->addColumns([
            'key' => [],
            'locales' => []
        ])->addAction('edit', [
            'href' => 'admin/translation/key/{key}',
            'icon' => 'edit'
        ])->setData($missing);

        $this->_view->table = $table;

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
                        'name' => 'old_password'
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
