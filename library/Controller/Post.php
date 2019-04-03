<?php

namespace TravelBlog\Controller;

use TravelBlog\Controller;
use TravelBlog\Content;
use TravelBlog\Model\Post as PostModel;

class Post extends Controller {
    public function byAction() {
        $postModel   = new PostModel;
        $allowedKeys = [
            'author',
            'country',
            'tag'
        ];

        $params = $this->_request->get('params');
        $where = array_intersect_key($params, array_flip($allowedKeys));
        $baseUrl = '';

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
            }

            unset($where[$key]);
        }

        $limit  = 10;
        $page   = $this->_request->getParam('page', 1);
        $offset = ($page - 1) * $limit;

        $this->_view->posts = $postModel->getPosts($where, $limit, $offset);

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

        $postModel = new PostModel;
        $postId = explode('-', $this->_request->get('action'))[0];

        $this->_view->template = 'post/post.phtml';
        $this->_view->post = $postModel->getPost($postId);
        $this->_view->pageTitle = strip_tags($this->_view->post['heading']);
        $this->_view->og = Content::getOpenGraph($this->_view->post);

    }
}
