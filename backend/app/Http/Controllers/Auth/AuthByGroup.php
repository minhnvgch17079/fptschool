<?php

namespace App\Http\Controllers\Auth;

class AuthByGroup {
    public static $groups = [
        1 => [
            'fileUpload/downloadFile'
        ],
        2 => [
            'fileUpload/downloadFile'
        ],
        3 => [
            'student/uploadAssignment',
            'faculty/getListActive',
            'student/getListSubmission',
            'fileUpload/downloadFile'
        ],
        4 => [],
        5 => [],
        6 => [],
        7 => [],
        8 => []
    ];
}
