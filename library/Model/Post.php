<?php

namespace TravelBlog\Model;

use Solsken\Model;

use TravelBlog\Model\PostMedia;

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
            'user.name (author)'
        ], ['post.id' => $id]);

        $postMediaModel = new PostMedia();

        $post['pics'] = $postMediaModel->getMedia($id);

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
            'user.name (author)',
            'files' => Medoo::raw('GROUP_CONCAT(<post_media.filename> ORDER BY <post_media.sort> ASC)')
        ], [
            'status' => $states,
            'GROUP' => 'post.id',
            'ORDER' => [$orderby => 'DESC'],
            'LIMIT' => [$offset, $limit],
        ]);

        foreach ($posts as $key => $post) {
            if ($post['files']) {
                $posts[$key]['files'] = explode(',', $post['files']);
            }
        }

        return $posts;
    }

    public function updatePost($data, $where = []) {
        $data['updated'] = time();
        $pics = [];

        if (isset($_SESSION['user']['id'])) {
            $userId = $_SESSION['user']['id'];
        } else {
            return false;
        }

        if (isset($data['pics'])) {
            $pics = $data['pics'];
            unset($data['pics']);
        }

        if (!isset($where['id'])) {
            $data['user_id'] = $userId;
            $data['created'] = time();
            $data['status'] = 'draft';

            $this->insert($data);
            $id = $this->id();
        } else {
            $previous = $this->get(['status'], $where);

            if ($data['status'] == 'active' && $previous['status'] != 'active') {
                $data['posted'] = time();
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
