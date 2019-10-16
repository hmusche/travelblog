<?php

namespace TravelBlog\Controller;

use TravelBlog\Controller;
use TravelBlog\Model\Post;

use Solsken\Profiler;

class Main extends Controller {
    public function indexAction() {
        Profiler::addBreakpoint('index-start');
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

        Profiler::addBreakpoint('index-got-pagination');

        $this->_view->posts = $postModel->getPosts([], $limit, $offset);
        $this->_view->pagination = $pagination;

        Profiler::addBreakpoint('index-got-posts');
    }
}
