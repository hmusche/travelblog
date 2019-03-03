<?php

namespace TravelBlog\Controller;

use TravelBlog\Controller;

use TravelBlog\Model\Post;

class Map extends Controller {
    public function routeAction() {
        $postModel = new Post;
        $posts = $postModel->getPostsSimple([], false);

        $this->_view->posts = $posts;
        $this->_view->includeMapGl = true;

        $maximum = $minimum = [false, false];

        foreach ($posts as $post) {
            if ($post['longitude']) {
                foreach (['longitude', 'latitude'] as $key => $type) {
                    if ($minimum[$key] === false || $post[$type] < $minimum[$key]) {
                        $minimum[$key] = $post[$type];
                    }

                    if ($maximum[$key] === false || $post[$type] > $maximum[$key]) {
                        $maximum[$key] = $post[$type];
                    }
                }
            }
        }

        $this->_view->boundaries = [$minimum, $maximum];
    }

    public function postsAction() {
        $return = [
            'status' => 'success',
            'posts'  => []
        ];

        $bounds = $this->_request->getParam('bounds');
        $postModel = new Post();

        $return['posts'] = $postModel->getPostsByBounds($bounds);

        echo json_encode($return);
        exit;
    }
}
