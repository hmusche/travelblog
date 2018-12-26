<?php

namespace TravelBlog\Model;

use Solsken\Model;

class Post extends Model {
    protected $_name = 'post';

    public function getPost($id) {
        return $this->get('*', ['id' => $id]);
    }

    public function getPosts($states = ['active'], $limit = 10, $offset = 0, $orderby = 'posted') {
        return $this->select([
            'id',
            'text',
            'title',
            'created',
            'updated',
            'posted',
            'status'
        ], [
            'status' => $states,
            'ORDER' => [$orderby => 'DESC'],
            'LIMIT' => [$offset, $limit],
        ]);
    }

    public function updatePost($data, $where = []) {
        $data['updated'] = time();

        if (isset($_SESSION['user']['id'])) {
            $userId = $_SESSION['user']['id'];
        } else {
            return false;
        }

        if (!isset($where['id'])) {
            $data['user_id'] = $userId;
            $data['created'] = time();
            return $this->insert($data);
        } else {
            $this->update($data, $where);

            return $where['id'];
        }
    }
}
