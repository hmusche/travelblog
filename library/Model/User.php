<?php

namespace TravelBlog\Model;

use TravelBlog\Model;

class User extends Model {
    protected $_name = 'user';

    public function login($login, $credential) {
        $user = $this->get('*', ['login' => $login]);

        var_dump($user);
    }
}
