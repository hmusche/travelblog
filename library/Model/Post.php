<?php

namespace TravelBlog\Model;

use Solsken\Model;
use Solsken\Util;

use TravelBlog\Model\PostMedia;
use TravelBlog\Model\PostMeta;
use TravelBlog\TimeZoneDb;
use TravelBlog\Content;

use Medoo\Medoo;

class Post extends Model {
    protected $_name = 'post';

    public function getPost($id) {
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

        $postMediaModel = new PostMedia();
        $postMetaModel  = new PostMeta();

        $post['files']   = $postMediaModel->getMedia($id);
        $post['meta']    = $postMetaModel->getMeta($id);
        $post['tag']     = isset($post['meta']['tag']) ? implode(', ', $post['meta']['tag']) : '';
        $post['slug']    = Util::getSlug($post['title']);
        $post['heading'] = $this->_getPostTitle($post);

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
            'count' => Medoo::raw('COUNT(*)')
        ], $where);

        return $count['count'];
    }

    public function getPosts($where = [], $limit = 10, $offset = 0, $orderby = 'posted') {
        $postMetaModel = new PostMeta;

        if (!isset($where['status'])) {
            $where['status'] = ['active'];
        }

        $where['GROUP'] = 'post.id';
        $where['ORDER'] = [$orderby => 'DESC'];
        $where['LIMIT'] = [$offset, $limit];

        $posts = $this->select([
            '[>]post_media' => ['id' => 'post_id'],
            '[>]post_meta' => ['id' => 'post_id'],
            '[>]user' => ['user_id' => 'id']
        ], [
            'post.id',
            'text',
            'title',
            'subtitle',
            'created',
            'updated',
            'posted',
            'status',
            'longitude',
            'latitude',
            'tz_offset',
            'user.name (author)',
            'files' => Medoo::raw('GROUP_CONCAT(<post_media.filename> ORDER BY <post_media.sort> ASC)')
        ], $where);

        foreach ($posts as $key => $post) {
            $posts[$key]['slug']    = Util::getSlug($this->_getPostTitle($post), 50);
            $posts[$key]['heading'] = $this->_getPostTitle($post);
            $posts[$key]['tag']     = implode(', ', $postMetaModel->getMeta($post['id'], 'tag'));

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
        } else {
            $previous = $this->get(['status', 'latitude', 'longitude', 'posted'], $where);

            $posted = $previous ? $previous['posted'] : null;

            if ($data['status'] == 'active' && $previous['status'] != 'active' && !$posted) {
                $data['posted'] = time();
                $posted = time();
            }

            if ($data['latitude']) {
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
        }

        if (isset($pics['name'][0]) && $pics['name'][0] !== '') {
            $postMediaModel = new PostMedia();
            $postMediaModel->handleUpload($id, $pics);
        }

        if ($metaData) {
            $metaModel = new PostMeta;

            foreach ($metaData as $type => $values) {
                $metaModel->setPostMetaType($id, $type, $values);
            }
        }

        return $id;
    }
}
