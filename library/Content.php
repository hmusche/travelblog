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

    static public function getMetaOpenGraph($meta) {
        return self::_getBaseOpenGraph(
            'article',
            $meta['value_formatted'],
            strip_tags($meta['text_formatted']),
            View::getInstance()->webhost . 'post/by/' . $meta['type'] . '/' . $meta['value']
        );
    }

    static public function getOpenGraph($post) {
        $view = View::getInstance();

        $description = strip_tags($post['text']);
        $description = substr($description, 0, strpos($description, '.') + 1);
        $description = htmlspecialchars($description);

        $openGraph = self::_getBaseOpenGraph(
            'article',
            $post['heading'],
            $description,
            $view->webhost . 'post/' . $post['id'] . '-' . $post['slug']
        );

        $openGraph['image'] = isset($post['files'][0])
                     ? $view->webhost . str_replace('{size}', 'xl', $post['files'][0]['full_path'])
                     : '';

        return $openGraph;
    }

    static protected function _getBaseOpenGraph($type, $title, $description, $url) {
        $config = Registry::get('app.config');

        $openGraph = [
            'site_name'   => $config['title'],
            'title'       => $title,
            'type'        => $type,
            'url'         => $url,
            'description' => $description,
            'image'       => ''
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
