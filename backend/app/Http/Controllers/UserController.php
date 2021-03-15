<?php

namespace App\Http\Controllers;

use App\Http\Services\UploadFile;
use App\Models\User;
use App\Models\Group;
use http\Url;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $a = new UploadFile();
        foreach ($files as $file) {
            $res = $a->uploadSingleFile($file);
            pd($res);
        }

    }
    // todo: api login
    public function login () {
        if (!empty(session()->get('info_user'))) responseToClient('Login success', true);
        $username = $this->request->post('username') ?? null;
        $password = $this->request->post('password') ?? null;

        if (empty($username)) responseToClient('Invalid username');
        if (empty($password)) responseToClient('Invalid password');

        $this->User = getInstance('User');

        $data = $this->User->getDataByUsername($username);

        if (empty($data)) responseToClient('Wrong username or password');

        if (!Hash::check($password, $data['password'])) responseToClient('Wrong username or password');

        session()->put('username', $username);
        session()->put('info_user', $data);
        session()->save();

        responseToClient('Login success', true, $data);
    }

    // todo: api logout
    public function logout () {
        session()->forget(['username', 'info_user']);
        session()->save();
        responseToClient('logout success',true);
    }

    // todo: api register
    public function register()
    {
        // missing only admin can access
        $username = $this->request->post('username') ?? null;
        $password = $this->request->post('password') ?? null;
        $groupId  = $this->request->post('group_id') ?? null;

        if (empty($username)) responseToClient('Invalid username');
        if (empty($password)) responseToClient('Invalid password');
        if (empty($groupId)) responseToClient('Invalid group id');
        $this->User = getInstance('User');
        $isExist = $this->User->isExist($username);

        if (!empty($isExist))      responseToClient('Username exist');
        if (strlen($username) < 4) responseToClient('Username must more than 3 characters');
        if (strlen($password) < 7) responseToClient('Password must more than 6 characters');

        $password = bcrypt($password);

        $dataSave = [
            'username' => $username,
            'password' => $password,
            'group_id' => $groupId
        ];

        $result = $this->User->insertData($dataSave);

        if ($result) responseToClient('Register success', true);
        responseToClient('Register failed');
    }

    // todo: api get users for admin
    public function getUser()
    {
        $username   =  $this->request->get('username')      ?? null;
        $fullName   = $this->request->get('full_name')      ?? null;
        $email      = $this->request->get('email')          ?? null;
        $phone      = $this->request->get('phone_number')   ?? null;
        $groupId    = $this->request->get('group_id')       ?? null;

        $this->User = getInstance('User');
        $data       = $this->User->getData($username, $fullName, $email, $phone, $groupId);
        if ($data) responseToClient('Get list users success', true, $data);
        responseToClient('No data found');
    }

    public function editUser()
    {
        $username = $this->request->post('username')    ?? null;
//        $fullName = $this->request->post('full_name')   ?? null;
//        $email    = $this->request->post('email')       ?? null;
//        $phone    = $this->request->post('phone_number')?? null;
//        $groupId  = $this->request->post('group_id')    ?? null;
//        $password = $this->request->post('password')    ?? null;



        if (empty($username)) responseToClient('please enter username');
//        if (empty($password)) responseToClient('please enter password');
//        if (empty($groupId))  responseToClient('please enter group id');
//        if (empty($fullName)) responseToClient('please enter full name');
//        if (empty($email))    responseToClient('please enter email');
//        if (empty($phone))    responseToClient('please enter phone');

        $this->User = getInstance('User');
//        $isExist = $this->User->isExist($username);

//        if (!empty($isExist))      responseToClient('Username exist');
        if (strlen($username) < 4) responseToClient('Username must more than 3 characters');
//        if (strlen($password) < 7)  return $this->responseToClient('Password must more than 6 characters');
//
//        $password  = bcrypt($password);

        $dataSave = [
            'username' => $username,
//            'password'     => $password,
//            'group_id'     => $groupId,
//            'full_name'    => $fullName,
//            'phone_number' => $phone,
//            'email'        => $email
        ];

        $result = $this->User->updateData($dataSave);

        if ($result) return $this->responseToClient('Edit success', true);
        return $this->responseToClient('Edit failed');
    }
}

