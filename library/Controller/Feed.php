<?php

namespace TravelBlog\Controller;

use TravelBlog\Controller;
use TravelBlog\Model\Post;
use TravelBlog\Model\PostMedia;

use Solsken\Feed\Rss;
use Solsken\Registry;

class Feed extends Controller {
    public function rssAction() {
        header("Content-Type: application/xml; charset=utf-8");

        $postModel = new Post;
        $postMediaModel = new PostMedia;

        $limit = 10;
        $posts = $postModel->getPosts([], $limit);

        $config = Registry::get('app.config');
        $feed = new Rss([
            'title' => $config['title'],
            'link' => $config['host'] . $config['path'],
            'description' => $config['description']
        ]);

        foreach ($posts as $post) {
            $item = [
                'title' => $post['title'],
                'link' => $config['host'] . $config['path'] . $post['link'],
                'description' => $post['text'],
                'guid' => $config['host'] . $config['path'] . 'post/' . $post['id'],
                'pubDate' => $post['posted']
            ];

            if ($post['files']) {
                $files = $postMediaModel->getMedia($post['id'], 'md');


                $item['enclosure'] = [
                    'url' => $config['host'] . $config['path'] . $files[0]['full_path'],
                    'type' => $files[0]['type'],
                    'length' => filesize("asset/{$post['id']}/md/{$files[0]['filename']}")
                ];

            }

            $feed->addItem($item);
        }

        echo $feed->render();
        exit;
    }
}
