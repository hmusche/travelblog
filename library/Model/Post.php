<?php

namespace TravelBlog\Model;

use Solsken\Model;
use Solsken\Util;
use Solsken\I18n;

use TravelBlog\Model\PostMedia;
use TravelBlog\Model\PostMeta;
use TravelBlog\Model\PostText;
use TravelBlog\TimeZoneDb;
use TravelBlog\Content;

use Medoo\Medoo;


class Post extends Model {
    protected $_name = 'post';

    public function getPost($id, $forceLocale = false) {
        $post = $this->get([
            '[><]user' => ['user_id' => 'id'],
        ], [
            'post.id',
            'title',
            'subtitle',
            'text',
            'posted',
            'created',
            'updated',
            'status',
            'longitude',
            'latitude',
            'tz_offset',
            'user.name (author)'
        ], ['post.id' => $id]);

        if (!$post) {
            return false;
        }

        $postMediaModel = new PostMedia();
        $postMetaModel  = new PostMeta();


        $post['files']   = $postMediaModel->getMedia($id);
        $post['meta']    = $postMetaModel->getMeta($id);
        $post['tag']     = isset($post['meta']['tag']) ? implode(', ', $post['meta']['tag']) : '';
        $post['slug']    = Util::getSlug($post['title']);
        $post['heading'] = $this->_getPostTitle($post);


        if (!isset($post['meta']['locale'])) {
            $post['meta']['locale'] = [Content::getLanguage($post['text'])];
            $postMetaModel->setPostMetaType($id, 'locale', $post['meta']['locale']);
        }

        $locale = I18n::getInstance()->getLocale(false);

        if ($forceLocale && !in_array($locale, $post['meta']['locale'])) {
            $postTextModel = new PostText;
            $post['text'] = $postTextModel->getText($id, $locale);
        }

        $post['text_formatted'] = Content::parse($post['text']);

        return $post;
    }

    public function getTotalPostCount($where = []) {
        if (!isset($where['status'])) {
            $where['status'] = ['active'];
        }

        $count = $this->get([
            '[>]post_meta' => ['id' => 'post_id'],
            '[>]user' => ['user_id' => 'id']
        ], [
            'count' => Medoo::raw('COUNT(DISTINCT <post.id>)')
        ], $where);

        return $count['count'];
    }

    public function getPostsByBounds($bounds) {
        $where = [
            'longitude[>=]' => $bounds[0][0],
            'longitude[<=]' => $bounds[1][0],
            'latitude[>=]' => $bounds[0][1],
            'latitude[<=]' => $bounds[1][1],
        ];

        return $this->getPosts($where, 0);
    }

    public function getPostsSimple($where = [], $limit = 10, $offset = 0, $orderby = 'posted') {
        if (!isset($where['status'])) {
            $where['status'] = ['active'];
        }

        $where['GROUP'] = 'post.id';
        $where['ORDER'] = [$orderby => 'DESC'];

        if ($limit) {
            $where['LIMIT'] = [$offset, $limit];
        }

        return $this->select([
            '[>]post_media' => ['id' => 'post_id'],
            '[>]post_meta' => ['id' => 'post_id'],
            '[>]user' => ['user_id' => 'id']
        ], [
            'post.id',
            'text',
            'title',
            'post.subtitle',
            'created',
            'updated',
            'posted',
            'status',
            'longitude',
            'latitude',
            'tz_offset',
            'user.name (author)',
            'tag' => Medoo::raw('GROUP_CONCAT(DISTINCT IF(<post_meta.type> = :tag, <post_meta.value>, NULL))', [':tag' => 'tag']),
            'language' => Medoo::raw('GROUP_CONCAT(DISTINCT IF(<post_meta.type> = :locale, post_meta.value, NULL))', [':locale' => 'locale']),
            'files' => Medoo::raw('GROUP_CONCAT(<post_media.filename> ORDER BY <post_media.sort> ASC)')
        ], $where);
    }

    public function getPosts($where = [], $limit = 10, $offset = 0, $orderby = 'posted') {
        $postMetaModel = new PostMeta;
        $posts = $this->getPostsSimple($where, $limit, $offset, $orderby);

        foreach ($posts as $key => $post) {
            $posts[$key]['slug']    = $post['status'] != 'waypoint' ? Util::getSlug($this->_getPostTitle($post), 50) : '';
            $posts[$key]['link']    = $post['status'] != 'waypoint' ? 'post/' . $post['id'] . "-" . $posts[$key]['slug'] : '';
            $posts[$key]['heading'] = $this->_getPostTitle($post);

            if ($post['files']) {
                $posts[$key]['files'] = explode(',', $post['files']);
            }
        }

        return $posts;
    }

    protected function _getPostTitle($post) {
        foreach (['title', 'subtitle', 'text'] as $key) {
            if (trim($post[$key]) !== '') {
                return $post[$key];
            }
        }

        return '';
    }

    public function updatePost($data, $where = []) {
        $metaModel = new PostMeta;
        $textModel = new PostText;
        $data['updated'] = time();
        $pics = [];

        if (isset($data['posted']) && !$data['posted']) {
            unset($data['posted']);
        }

        if (isset($_SESSION['user']['id'])) {
            $userId = $_SESSION['user']['id'];
        } else {
            return false;
        }

        if (isset($data['files'])) {
            $pics = $data['files'];
            unset($data['files']);
        }

        $metaData = [];

        foreach (PostMeta::getMetaKeys() as $metaKey) {
            if (array_key_exists($metaKey, $data)) {
                $metaData[$metaKey] = $data[$metaKey];
                unset($data[$metaKey]);
            }
        }

        if (!isset($where['id'])) {
            $data['user_id'] = $userId;
            $data['created'] = time();
            $data['status'] = 'draft';

            if ($data['latitude']) {
                $tzData = (new TimeZoneDb())->getTimeZoneData($data['latitude'], $data['longitude'], time());

                if ($tzData) {
                    $data['tz_offset'] = $tzData['offset'];
                    $metaData['country'] = $tzData['cc'];
                }
            }

            $this->insert($data);
            $id = $this->id();

            $metaData['locale'] = Content::getLanguage($data['text']);
        } else {
            $previous = $this->get(['status', 'latitude', 'longitude', 'posted', 'text'], $where);

            $posted = $previous ? $previous['posted'] : null;

            if (isset($data['status'])
                && in_array($data['status'], ['active', 'waypoint'])
                && $previous['status'] != $data['status']
                && !$posted) {
                $data['posted'] = time();
                $posted = time();
            }

            if (isset($data['latitude']) && $data['latitude']) {
                if ($data['latitude'] != $previous['latitude'] || $data['longitude'] != $previous['longitude']) {
                    $tzData = (new TimeZoneDb())->getTimeZoneData($data['latitude'], $data['longitude'], $posted);

                    if ($tzData) {
                        $data['tz_offset'] = $tzData['offset'];
                        $metaData['country'] = $tzData['cc'];
                    }

                }
            }

            $this->update($data, $where);

            $id = $where['id'];

            // Delete translated text if text was changed
            if (($previous && $previous['text'] != $data['text'])) {
                $textModel->delete(['post_id' => $id]);
            }

            // Set Locale of post if not already set
            $supportedLocales = I18n::getInstance()->getSupportedLocales();
            $supportedLocales = array_map(function($value) {
                return substr($value, 0, 2);
            }, $supportedLocales);
            
            if (array_intersect($metaModel->getMeta($id, 'locale'), $supportedLocales) === []) {
                $metaData['locale'] = Content::getLanguage($data['text']);
            }
        }

        if (isset($pics['name'][0]) && $pics['name'][0] !== '') {
            $postMediaModel = new PostMedia();
            $postMediaModel->handleUpload($id, $pics);
        }

        if ($metaData) {
            foreach ($metaData as $type => $values) {
                if ($values !== false) {
                    $metaModel->setPostMetaType($id, $type, $values);
                }
            }
        }

        return $id;
    }

}
