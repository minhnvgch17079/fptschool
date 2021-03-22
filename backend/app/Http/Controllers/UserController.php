<?php

namespace App\Http\Controllers;

use App\Http\Middleware\Authentication;
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
        if (!empty(session()->get('info_user'))) responseToClient('Login success', true, session()->get('info_user'));
        $username = $this->request->post('username') ?? null;
        $password = $this->request->post('password') ?? null;

        if (empty($username)) responseToClient('Invalid username');
        if (empty($password)) responseToClient('Invalid password');

        $this->User = getInstance('User');

        $data = $this->User->getDataByUsername($username);

        if (empty($data['is_active'])) responseToClient('Your account is blocking. Please contact admin for help');

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
        $username   = $this->request->get('username')       ?? null;
        $fullName   = $this->request->get('full_name')      ?? null;
        $email      = $this->request->get('email')          ?? null;
        $phone      = $this->request->get('phone_number')   ?? null;
        $groupId    = $this->request->get('group_id')       ?? null;

        $this->User = getInstance('User');
        $data       = $this->User->getData($username, $fullName, $email, $phone, $groupId);
        if ($data) responseToClient('Get list users success', true, $data);
        responseToClient('No data found');
    }

    public function updateProfile() {

        $dataUpdate = $this->request->post('update')  ?? null;

        $fullName   = $dataUpdate['full_name']        ?? null;
        $phone      = $dataUpdate['phone_number']     ?? null;
        $email      = $dataUpdate['email']            ?? null;
        $age        = $dataUpdate['age']              ?? null;
        $dateBirth  = $dataUpdate['DATE_of_birth']    ?? null;

        if (!empty($fullName) && strlen($fullName) > 200) responseToClient('Full name must smaller than 200 characters');
        if (!empty($phone)) {
            if (!is_numeric($phone) || strlen($phone) !== 10) responseToClient('Invalid phone');
        }
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) responseToClient('Invalid email');
        if (!empty($age)) {
            if (!is_numeric($age) || $age < 18 || $age > 30) responseToClient('Age must 18 to 30');
        }
        if (!empty($dateBirth)) {
            if (!validateDate($dateBirth, 'Y-m-d')) responseToClient('Invalid date of birth');
        }

        $infoUpdate = session()->get('info_user');
        $idAction   = $infoUpdate['id'];
        $idUpdate   = $infoUpdate['id'];

        if ($idAction != $idUpdate) responseToClient('No permission for update other account');

        $this->User = getInstance('User');

        $dataSave = [
            'full_name'     => $fullName,
            'phone_number'  => $phone,
            'email'         => $email,
            'age'           => $age,
            'DATE_of_birth' => $dateBirth,
            'modified'      => date('Y-m-d H:i:s'),
            'modified_by'   => $idUpdate
        ];

        $result = $this->User->updateById($dataSave, $idUpdate);

        if ($result) responseToClient('Update success', true);
        responseToClient('Update failed');
    }

    public function getInfoUser () {
        $infoUser = session()->get('info_user');
        $username = $infoUser['username'];

        $this->User = getInstance('User');
        $data = $this->User->getData($username, null, null, null, null);

        if (!empty($data)) responseToClient('Get info success', true, $data[0]);
        responseToClient('No data user found');
    }

    public function changePassword () {
        $oldPassword = $this->request->post('old_pass') ?? null;
        $newPass     = $this->request->post('new_pass') ?? null;
        $rePass      = $this->request->post('re_pass')  ?? null;
        $infoUser    = session()->get('info_user');

        if ($newPass != $rePass)  responseToClient('Password and confirm not match');
        if (strlen($newPass) < 7) responseToClient('Password must more than 6 characters');

        $this->User = getInstance('User');
        $data = $this->User->getDataByUsername($infoUser['username']);

        if (empty($data)) responseToClient('Wrong username or password');

        if (!Hash::check($oldPassword, $data['password'])) responseToClient('Wrong old password');

        $dataUpdate = [
            'password' => bcrypt($newPass)
        ];

        $result = $this->User->updateById($dataUpdate, $infoUser['id']);

        if (!empty($result)) responseToClient('Change password success', true);
        responseToClient('Change password failed');
    }

    public function disableUser () {
        $id = $this->request->get('id') ?? null;

        if (empty($id)) responseToClient('Id user is invalid');

        $this->User = getInstance('User');

        $dataUpdate = [
            'is_active' => 0,
            'modified_by' => Authentication::$info['id'] ?? null
        ];

        $result = $this->User->updateById($dataUpdate, $id);

        if ($result) responseToClient('Disable account success', true);
        responseToClient('Failed to disable account');
    }
}

