<?php

namespace TravelBlog\Controller;

use TravelBlog\Controller;
use Solsken\Registry;

use TravelBlog\Model\Post;
use TravelBlog\Model\Stat;

class Map extends Controller {
    public function routeAction() {
        $postModel = new Post;
        $statModel = new Stat;
        $from = strtotime(Registry::get('app.config')['journey']['start']);
        $posts = $postModel->getPosts([
            'post.posted[>=]' => $from,
            'status' => ['active', 'waypoint']
        ], false);
        $markers = [];

        $this->_view->includeMapGl = true;

        $maximum = $minimum = [false, false];

        foreach ($posts as $post) {
            if ($post['longitude']) {
                $markers[] = [
                    'longitude' => $post['longitude'],
                    'latitude'  => $post['latitude'],
                    'heading'   => $post['heading'],
                    'link'      => $post['link']
                ];

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

        $this->_view->markers = $markers;
        $this->_view->boundaries = [$minimum, $maximum];

        $this->_view->stats = $statModel->getFormattedStats('pie');
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
