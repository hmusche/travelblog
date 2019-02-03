<?php

namespace TravelBlog;
use Solsken\View;

class Content {
    static public function parse($text) {
        $text = nl2br($text);

        return $text;
    }

    static public function getOpenGraph($post) {
        $view = View::getInstance();

        $openGraph = [
            'title' => $post['heading'],
            'type'  => 'article',
            'url'   => $view->webhost . 'post/' . $post['id'] . '-' . $post['slug'],
            'image' => isset($post['files'][0])
                     ? $view->webhost . $post['files'][0]['full_path']
                     : ''
        ];

        return $openGraph;
    }
}
