<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Group;
class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {


    }
    public function add()
    {
        $username  = $_POST['username'] ?? null;
        $password  = $_POST['password'] ?? null;
        $groupId   = $_POST['group_id'] ?? null;
        //full name, phone number, email,....
        $userModel = new User();

        if (empty($username)) return $this->responseToClient('please enter username');
        if (empty($password)) return $this->responseToClient('please enter password');
        if (empty($groupId))  return $this->responseToClient('please enter group id');

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

        if ($result) return $this->responseToClient('create user success', true);
        return $this->responseToClient('create user failed');

    }
    public function adddlosuredate()
    {

    }
}
