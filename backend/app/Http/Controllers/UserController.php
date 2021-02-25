<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    public function __construct() {
        session_start();
    }

    // todo: api login
    public function login () {
        $username = $_POST['username'] ?? null;
        $password = $_POST['password'] ?? null;

        if (empty($username)) return $this->responseToClient('Invalid username');
        if (empty($password)) return $this->responseToClient('Invalid password');

        $userModel = new User();


        $data      = $userModel->getDataByUsername($username);


        if (empty($data)) return $this->responseToClient('Wrong username or password');

        if (!Hash::check($password, $data['password'])) return $this->responseToClient('Wrong username or password');

        $_SESSION['username'] = $username;


        return $this->responseToClient('Login success', true);

    }

    // todo: api logout
    public function logout () {
        // destroy session
        $username = $_GET['username'] ?? null;
        
        $_SESSION['username'] = $username;
        unset($_SESSION['username']);

        return $this->responseToClient('logout success',true);

    }

    public function register () {
        // missing only admin can access
        $username  = $_POST['username'] ?? null;
        $password  = $_POST['password'] ?? null;
        $groupId   = $_POST['group_id'] ?? null;
        $userModel = new User();


        if (empty($username)) return $this->responseToClient('Invalid username');
        if (empty($password)) return $this->responseToClient('Invalid password');
        if (empty($groupId))  return $this->responseToClient('Invalid group id');

        $isExist   = $userModel->isExist($username);

        if (!empty($isExist))       return $this->responseToClient('Username exist');
        if (strlen($username) < 4)  return $this->responseToClient('Username must more than 3 characters');
        if (strlen($password) < 7)  return $this->responseToClient('Password must more than 6 characters');

        $password  = bcrypt($password);

        $dataSave  = [
            'username' => $username,
            'password' => $password,
            'group_id' => $groupId
        ];

        $result    = $userModel->insertData($dataSave);

        if ($result) return $this->responseToClient('Register success', true);
        return $this->responseToClient('Register failed');
    }

}
