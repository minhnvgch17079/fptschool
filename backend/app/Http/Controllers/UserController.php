<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


/**
 * Class UserController
 * @package App\Http\Controllers
 * @property User User
 */


class UserController extends Controller
{
    public function test () {
        $files = $this->request->file('files') ?? null;

        if (empty($files)) responseToClient('Invalid file');

        foreach ($files as $file) {
            $result = $file->move(base_path().'/public/files/', $file->getClientOriginalName());
            echo $result;
        }

    }
    // todo: api login
    public function login () {
        if (!empty($_SESSION['username'])) responseToClient('Login success', true);
        $username = $this->request->post('username') ?? null;
        $password = $this->request->post('password') ?? null;

        if (empty($username)) responseToClient('Invalid username');
        if (empty($password)) responseToClient('Invalid password');

        $userModel = new User();
        $data      = $userModel->getDataByUsername($username);

        if (empty($data)) responseToClient('Wrong username or password');

        if (!Hash::check($password, $data['password'])) responseToClient('Wrong username or password');

        $_SESSION['username']  = $username;
        $_SESSION['info_user'] = $data;

        responseToClient('Login success', true, $data);
    }

    // todo: api logout
    public function logout () {
        session_destroy();
        responseToClient('logout success',true);
    }

    // todo: api register
    public function register () {
        // missing only admin can access
        $username  = $_POST['username'] ?? null;
        $password  = $_POST['password'] ?? null;
        $groupId   = $_POST['group_id'] ?? null;
        $userModel = new User();

//        if (($_SESSION['username'] ?? null) != 'admin') responseToClient('No access permission');

        if (empty($username)) responseToClient('Invalid username');
        if (empty($password)) responseToClient('Invalid password');
        if (empty($groupId))  responseToClient('Invalid group id');

        $isExist   = $userModel->isExist($username);

        if (!empty($isExist))       responseToClient('Username exist');
        if (strlen($username) < 4)  responseToClient('Username must more than 3 characters');
        if (strlen($password) < 7)  responseToClient('Password must more than 6 characters');

        $password  = bcrypt($password);

        $dataSave  = [
            'username' => $username,
            'password' => $password,
            'group_id' => $groupId
        ];

        $result    = $userModel->insertData($dataSave);

        if ($result) responseToClient('Register success', true);
        responseToClient('Register failed');
    }

    // todo: api get users for admin
    public function getUser () {
//        if (($_SESSION['username'] ?? null) != 'admin') responseToClient('No access permission');
        $username = $this->request->get('username')     ?? null;
        $fullName = $this->request->get('full_name')    ?? null;
        $email    = $this->request->get('email')        ?? null;
        $phone    = $this->request->get('phone_number') ?? null;

        $this->User = getInstance('User');
        $data       = $this->User->getData($username, $fullName, $email, $phone);
        if ($data) responseToClient('Get list users success', true, $data);
        responseToClient('No data found');
    }

}
