<?php

namespace App\Http\Controllers;

use App\Http\Components\AuthComponent;
use App\Http\Middleware\Authentication;
use App\Http\Services\UploadFile;
use App\Models\FileUpload;
use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Facades\Hash;


/**
 * Class UserController
 *
 * @package App\Http\Controllers
 * @property User User
 * @property FileUpload FileUpload
 * @property Group Group
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
        if (!empty(AuthComponent::user())) responseToClient('Login success', true, AuthComponent::user());
        $username = $this->request->post('username') ?? null;
        $password = $this->request->post('password') ?? null;

        if (empty($username)) responseToClient('Invalid username');
        if (empty($password)) responseToClient('Invalid password');

        $this->User = getInstance('User');

        $data = $this->User->getDataByUsername($username);

        if (empty($data)) responseToClient('Wrong username or password');

        if (empty($data['is_active'])) responseToClient('Your account is blocking. Please contact admin for help');

        if (!Hash::check($password, $data['password'])) responseToClient('Wrong username or password');

        AuthComponent::setUserLogin($data);

        responseToClient('Login success', true, $data);
    }

    // todo: api logout
    public function logout () {
        AuthComponent::setUserLogout();
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

        $infoUpdate = AuthComponent::user();
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
        $infoUser = AuthComponent::user();
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
        $infoUser    = AuthComponent::user();

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
            'is_active'     => 0,
            'modified_by'   => Authentication::$info['id'] ?? null
        ];

        $result = $this->User->updateById($dataUpdate, $id);

        if ($result) responseToClient('Disable account success', true);
        responseToClient('Failed to disable account');
    }

    public function updateAvatar () {
        $avatar = $this->request->file('img') ?? null;

        if (empty($avatar)) responseToClient('Invalid avatar for set');

        $fileUpload = new UploadFile();

        $result     = $fileUpload->uploadSingleFile($avatar);

        if (empty($result)) responseToClient('Upload avatar failed');

        $dataUpdate = [
            'image' => $result
        ];

        $this->User = getInstance('User');
        $result = $this->User->updateById($dataUpdate, AuthComponent::user('id'));

        if ($result) responseToClient('Upload avatar success', true);
        responseToClient('Upload avatar failed, please try again');
    }

    public function getAvatar () {
        $image = AuthComponent::user('image') ?? null;

        if (empty($image)) responseToClient('Please upload your image');

        $this->FileUpload = getInstance('FileUpload');

        $dataImage = $this->FileUpload->getFileInfoById($image, null);

        if (empty($dataImage)) responseToClient('Please upload your image');

        $url = public_path(). "/files/" .$dataImage['file_path'];

        try {
            $img = file_get_contents($url);
            return response($img)->header('Content-type','image/png');
        } catch (\Exception $exception) {
            logErr($exception->getMessage());
        }
        responseToClient('Cannot get avatar');
    }

    public function getAllGroup () {
        $this->Group = getInstance('Group');
        $data        = $this->Group->getAllGroup();

        if (empty($data)) responseToClient('No group found');

        responseToClient('Get list group success', true, $data);
    }

    public function report () {
        $groupId    = $this->request->get('group_id') ?? null;
        $this->User = getInstance('User');
        $data       = $this->User->getData(null, null, null, null, $groupId, null);

        $dataReturn = [];

        foreach ($data as $datum) {
            $groupName = $datum['group_name'];
            $dataReturn['total_account'] =  $dataReturn['total_account'] ?? 0;
            $dataReturn['total_account']++;
            $dataReturn['detail'][$groupName] = $dataReturn['detail'][$groupName] ?? 0;
            $dataReturn['detail'][$groupName]++;
        }

        responseToClient('Get report user success', true, $dataReturn);
    }
}

