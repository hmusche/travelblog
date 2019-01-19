<?php

namespace TravelBlog\Controller;

use TravelBlog\Controller;
use TravelBlog\Model\Post as PostModel;

class Post extends Controller {
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
