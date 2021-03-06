<?php

namespace TravelBlog\Controller;

use TravelBlog\Controller;
use TravelBlog\Content;
use TravelBlog\Model\Post as PostModel;
use TravelBlog\Model\Meta as MetaModel;
use Solsken\Util;
use Solsken\Cookie;

class Post extends Controller {
    public function byAction() {
        $postModel   = new PostModel;
        $metaModel   = new MetaModel;
        $allowedKeys = [
            'author',
            'country',
            'tag'
        ];

        $params  = $this->_request->get('params');
        $where   = array_intersect_key($params, array_flip($allowedKeys));
        $baseUrl = '';
        $meta    = '';

        foreach ($where as $key => $value) {
            $baseUrl .= "$key/$value/";
            $value = strtolower($value);

            if (strpos($value, ',') !== false) {
                $value = explode(',', $value);
            }

            if ($key == 'author') {
                $where['user.name'] = $value;
            } else {
                $where['post_meta.type']  = $key;
                $where['post_meta.value'] = $value;
                $meta = $metaModel->getMetaByTypeAndValue($key, $value);
            }

            unset($where[$key]);
        }

        $limit  = 10;
        $page   = $this->_request->getParam('page', 1);
        $offset = ($page - 1) * $limit;

        $pathParts = array_intersect_key($params, array_flip($allowedKeys));
        $pathParts = array_map(function($key) use ($pathParts) {
            return $key . '/' . $pathParts[$key];
        }, array_flip($pathParts));

        $this->_view->posts     = $postModel->getPosts($where, $limit, $offset);
        $this->_view->meta      = $meta;
        $this->_view->canonical = $this->_view->webhost . 'post/by/' . implode('/', $pathParts);

        if ($meta) {
            $this->_view->og = Content::getMetaOpenGraph($meta);
        }

        $this->_view->pagination = [
            'page' => $page,
            'limit' => $limit,
            'totalCount' => $postModel->getTotalPostCount($where, $limit, $offset),
            'baseUrl' => 'post/by/' . $baseUrl
        ];

    }

    public function __call($method, $args) {
        if (strpos($method, 'Action') === false) {
            throw new \Exception('Method not found');
        }

        $postModel      = new PostModel;
        $postId         = explode('-', $this->_request->get('action'))[0];
        $forceTranslate = (bool)$this->_request->getParam('trans', Cookie::get('post_translate', false));

        $post = $postModel->getPost($postId, $forceTranslate);

        if (!$post) {
            throw new \Exception('Post not found', 404);
        }

        if ($post['status'] != 'active') {
            session_start();

            if (!Util::isLoggedIn()) {
                throw new \Exception('Not allowed', 404);
            }
        }

        $this->_view->template  = 'post/post.phtml';
        $this->_view->pageTitle = strip_tags($post['heading']);
        $this->_view->og        = Content::getOpenGraph($post);
        $this->_view->canonical = $this->_view->og['url'];

        if (!$post['posted']) {
            $post['posted'] = time();
        }

        $this->_view->post = $post;
    }
}
