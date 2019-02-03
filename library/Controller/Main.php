<?php

namespace TravelBlog\Controller;

use TravelBlog\Controller;

use TravelBlog\Model\Post;

class Main extends Controller {
    public function indexAction() {
        $postModel = new Post;

        $limit  = 10;
        $page   = $this->_request->getParam('page', 1);
        $offset = ($page - 1) * $limit;

        $pagination = [
            'page' => $page,
            'limit' => $limit,
            'totalCount' => $postModel->getTotalPostCount(),
            'baseUrl' => ''
        ];

        $this->_view->posts = $postModel->getPosts([], $limit, $offset);
        $this->_view->pagination = $pagination;
    }
}
