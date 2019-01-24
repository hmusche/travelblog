<?php

namespace TravelBlog\Controller;

use TravelBlog\Controller;
use TravelBlog\Model\Post as PostModel;

class Post extends Controller {
    public function byAction() {
        $postModel   = new PostModel;
        $allowedKeys = [
            'country',
            'tag'
        ];

        $where = array_intersect_key($this->_request->get('params'), array_flip($allowedKeys));

        foreach ($where as $key => $value) {
            $value = strtolower($value);

            if (strpos($value, ',') !== false) {
                $value = explode(',', $value);
            }

            $where['post_meta.type']  = $key;
            $where['post_meta.value'] = $value;
            unset($where[$key]);
        }

        $this->_view->posts = $postModel->getPosts($where);

    }

    public function __call($method, $args) {
        if (strpos($method, 'Action') === false) {
            throw new \Exception('Method not found');
        }

        $postModel = new PostModel;
        $postId = explode('-', $this->_request->get('action'))[0];

        $this->_view->template = 'post/post.phtml';
        $this->_view->post = $postModel->getPost($postId);

    }
}
