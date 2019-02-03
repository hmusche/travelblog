<?php

namespace TravelBlog;
use Solsken\View;
use Solsken\Registry;

class Content {
    static public function parse($text) {
        $text = nl2br($text);

        return $text;
    }

    static public function getOpenGraph($post) {
        $view = View::getInstance();
        $config = Registry::get('app.config');

        $openGraph = [
            'site_name' => $config['title'],
            'title' => $post['heading'],
            'type'  => 'article',
            'url'   => $view->webhost . 'post/' . $post['id'] . '-' . $post['slug'],
            'image' => isset($post['files'][0])
                     ? $view->webhost . str_replace('{size}', 'xl', $post['files'][0]['full_path'])
                     : ''
        ];



        return $openGraph;
    }
}
