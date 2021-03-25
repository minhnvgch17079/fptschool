<?php

namespace App\Http\Controllers\Auth;

class AuthByGroup {
    public static $groups = [
        1 => [
            'fileUpload/downloadFile',
            'fileUpload/disabledFile',
            'user/disableUser',
            'admin/getAllError',
            'admin/addUserToFaculty',

        ],
        2 => [
            'fileUpload/downloadFile',
            'fileUpload/disabledFile',
            'marketing-co/getListSubmission',
        ],
        3 => [
            'student/uploadAssignment',
            'faculty/getListActive',
            'student/getListSubmission',
            'fileUpload/downloadFile',
            'fileUpload/disabledFile',
        ],
        4 => [
            'fileUpload/downloadFile',
            'fileUpload/readPdfFile',
            'student/getListSubmission',
            'faculty/getListActive',
            'marketing-ma/getNumContriForFaculty'
        ],
        5 => [],
        6 => [],
        7 => [],
        8 => []
    ];
}
