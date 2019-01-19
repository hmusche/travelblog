<?php

namespace TravelBlog\Model;

use Solsken\Model;
use Solsken\Util;

use TravelBlog\Model\PostMedia;
use TravelBlog\TimeZoneDb;

use Medoo\Medoo;

class Post extends Model {
    protected $_name = 'post';

    public function getPost($id) {
        $post = $this->get([
            '[><]user' => ['user_id' => 'id']
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
            'user.name (author)'
        ], ['post.id' => $id]);

        $postMediaModel = new PostMedia();

        $post['files'] = $postMediaModel->getMedia($id);

        $post['slug'] = Util::getSlug($post['title']);

        return $post;
    }

    public function getPosts($states = ['active'], $limit = 10, $offset = 0, $orderby = 'posted') {
        $posts = $this->select([
            '[>]post_media' => ['id' => 'post_id'],
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
            'user.name (author)',
            'files' => Medoo::raw('GROUP_CONCAT(<post_media.filename> ORDER BY <post_media.sort> ASC)')
        ], [
            'status' => $states,
            'GROUP' => 'post.id',
            'ORDER' => [$orderby => 'DESC'],
            'LIMIT' => [$offset, $limit],
        ]);

        foreach ($posts as $key => $post) {
            $posts[$key]['slug']    = Util::getSlug($this->_getPostTitle($post), 50);
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
        $data['updated'] = time();
        $pics = [];

        if (isset($data['posted']) && ! $data['posted']) {
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

        if (!isset($where['id'])) {
            $data['user_id'] = $userId;
            $data['created'] = time();
            $data['status'] = 'draft';

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
                    $data['tz_offset'] = (new TimeZoneDb())->getTimeZoneOffset($data['latitude'], $data['longitude'], $posted);
                }
            }

            $this->update($data, $where);

            $id = $where['id'];
        }

        if (isset($pics['name'][0]) && $pics['name'][0] !== '') {
            $postMediaModel = new PostMedia();
            $postMediaModel->handleUpload($id, $pics);
        }

        return $id;
    }
}
