<?php


namespace TravelBlog\Model;

use Solsken\Model;
use Solsken\Util;
use Solsken\Registry;

use TravelBlog\Model\PostMeta;
use TravelBlog\Model\Post;
use TravelBlog\Content;

use Medoo\Medoo;

use Google\Cloud\Translate\TranslateClient;

class PostText extends Model {
    protected $_name = 'post_text';

    public function getText($postId, $locale) {
        $text = $this->get([
            'text'
        ], [
            'post_id' => $postId,
            'locale' => $locale
        ]);

        if ($text) {
            return $text['text'];
        }

        $postModel = new Post;
        $post      = $postModel->get(['text'], ['id' => $postId]);

        if (!$post || trim($post['text']) === '') {
            return '';
        }

        $translation = Content::getTranslation($post['text'], $locale);

        if ($translation) {
            $res = $this->insert([
                'post_id' => $postId,
                'locale'  => $locale,
                'text'    => $translation
            ]);

            return $translation;
        }

        return '';
    }
}
