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
        return $this->select([
            '[>]post_media' => ['id' => 'post_id'],
            '[>]user' => ['user_id' => 'id']
        ], [
            'post.id',
            'text',
            'title',
            'created',
            'updated',
            'posted',
            'status',
            'user.name (author)',
            'files' => Medoo::raw('GROUP_CONCAT(<post_media.filename>)')
        ], [
            'status' => $states,
            'GROUP' => 'post.id',
            'ORDER' => [$orderby => 'DESC'],
            'LIMIT' => [$offset, $limit],
        ]);
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
            $id = $this->insert($data);
        } else {
            $previous = $this->get(['status'], $where);

            if ($data['status'] == 'active' && $previous['status'] != 'active') {
                $data['posted'] = time();
            }

            $this->update($data, $where);

            $id = $where['id'];
        }

        if ($pics !== []) {
            $postMediaModel = new PostMedia();
            $postMediaModel->handleUpload($id, $pics);
        }

        return $id;
    }
}
