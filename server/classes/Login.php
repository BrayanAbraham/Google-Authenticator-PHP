<?php

class Login {

    private $username;
    private $password;

    public function __construct() {
        $credentials = file_get_contents('../data/auth.json');
        $credentials = json_decode($credentials, true);

        $this->username = $credentials['username'];
        $this->password = $credentials['password'];
    }

    public function login($username, $password) {
        return $username === $this->username && $password === $this->password;
    }
}