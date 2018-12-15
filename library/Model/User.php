<?php

namespace TravelBlog\Model;

use TravelBlog\Model;

class User extends Model {
    protected $_name = 'user';

    public function login($login, $credential) {
        $user = $this->get('*', ['login' => $login]);

        var_dump($user);
    }

    public function setPassword($data) {
        if (!isset($data['login']) || trim($data['login']) === '') {
            return false;
        }

        $user = $this->get('*', ['login' => $data['login']]);

        if ($user['password'] && (!isset($data['old_password']) || password_verify($data['old_password'], $user['password']))) {
            return false;
        }

        return $this->update([
            'password' => password_hash($data['password'], PASSWORD_DEFAULT)
        ], [
            'id' => $user['id']
        ]);
    }
}
