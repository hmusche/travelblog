<?php

namespace TravelBlog\Controller;

use TravelBlog\Controller;

use TravelBlog\Model\Post;

class Main extends Controller {
    public function indexAction() {
        
        $postModel = new Post;

        $this->_view->posts = $postModel->getPosts();
    }
}
