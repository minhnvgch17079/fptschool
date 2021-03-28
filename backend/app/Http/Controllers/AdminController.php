<?php

namespace App\Http\Controllers;

use App\Models\CoordinatorFaculty;
use App\Models\Log;
use App\Models\User;

/**
 * Class AdminController
 * @package App\Http\Controllers
 * @property Log Log
 * @property User User
 * @property CoordinatorFaculty CoordinatorFaculty
 */

class AdminController extends Controller
{
    public function getAllError () {
        $this->Log = getInstance('Log');

        $dataError = $this->Log->getAllError();

        if (!empty($dataError)) responseToClient('Get error success', true, $dataError);
        responseToClient('No error found');
    }

    public function addUserToFaculty () {
        $userId     = $this->request->get('user_id')    ?? null;
        $facultyId  = $this->request->get('faculty_id') ?? null;

        if (empty($userId))     responseToClient('Invalid user');
        if (empty($facultyId))  responseToClient('Invalid faculty');

        $this->User = getInstance('User');

        $is = $this->User->isAddFaculty($userId);

        if (empty($is)) responseToClient('Only student or marketing coordinator');

        $this->CoordinatorFaculty = getInstance('CoordinatorFaculty');

        $isExist = $this->CoordinatorFaculty->isExist($userId, $facultyId);

        if (!empty($isExist)) responseToClient('User was added for this faculty');

        $dataSave = [
            'user_id'    => $userId,
            'faculty_id' => $facultyId
        ];

        $result = $this->CoordinatorFaculty->save($dataSave);

        if ($result) responseToClient('Add marketing coordinator for faculty success', true);
        responseToClient('Failed to add marketing coordinator for faculty');
    }
}
