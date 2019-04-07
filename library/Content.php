<?php

namespace TravelBlog;
use Solsken\View;
use Solsken\Registry;
use Google\Cloud\Translate\TranslateClient;

class Content {
    static public function parse($text) {
        $text = nl2br($text);

        return $text;
    }

    static public function getOpenGraph($post) {
        $view = View::getInstance();
        $config = Registry::get('app.config');

        $description = strip_tags($post['text']);
        $description = substr($description, 0, strpos($description, '.') + 1);

        $openGraph = [
            'site_name' => $config['title'],
            'title' => $post['heading'],
            'type'  => 'article',
            'url'   => $view->webhost . 'post/' . $post['id'] . '-' . $post['slug'],
            'description' => $description,
            'image' => isset($post['files'][0])
                     ? $view->webhost . str_replace('{size}', 'xl', $post['files'][0]['full_path'])
                     : ''
        ];



        return $openGraph;
    }

    static public function getLanguage($text) {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=config/google.json');

        $translate = new TranslateClient([
            'projectId' => Registry::get('app.config')['google']['project_id']
        ]);

        $result = $translate->detectLanguage($text);

        if (isset($result['languageCode'])) {
            return $result['languageCode'];
        }

        return false;
    }

    static public function getTranslation($text, $language) {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=config/google.json');

        $translate = new TranslateClient([
            'projectId' => Registry::get('app.config')['google']['project_id']
        ]);

        $result = $translate->translate($text, [
            'target' => $language
        ]);

        if (isset($result['text'])) {
            return $result['text'];
        }

        return false;
    }
}
