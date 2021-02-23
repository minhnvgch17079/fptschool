<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{

    // todo: api login
    public function login () {
        $username = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;

        if (empty($username)) return $this->responseToClient('Invalid username');
        if (empty($password)) return $this->responseToClient('Invalid password');

        $userModel = new User();
        $data      = $userModel->getDataByUsername($username);

        if (empty($data)) return $this->responseToClient('Wrong username or password');

        if ($password != $data['password']) return $this->responseToClient('Wrong username or password');

        // create session

        return $this->responseToClient('Login success', true);
    }

    // todo: api logout
    public function logout () {
        // destroy session
    }

}
