<?php

namespace TravelBlog\Model;

use Solsken\Model;

class User extends Model {
    protected $_name = 'user';

    public function login($data) {
        $user = $this->get('*', ['login' => $data['login']]);

        if ($user && password_verify($data['password'], $user['password'])) {
            session_regenerate_id();
            unset($user['password']);

            $_SESSION['user'] = $user;

            return true;
        } else {
            return false;
        }
    }

    public function setPassword($data) {
        if (!isset($data['login']) || trim($data['login']) === '') {
            return false;
        }

        $user = $this->get(['id', 'password'], ['login' => $data['login']]);

        if ($user['password'] && (!isset($data['old_password']) || password_verify($data['old_password'], $user['password']))) {
            return false;
        }

        return $this->update([
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'token'    => null
        ], [
            'id' => $user['id']
        ]);
    }
}
