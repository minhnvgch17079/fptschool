<?php

namespace App\Http\Controllers\Auth;

class AuthByGroup {
    public static $groups = [
        1 => [
            'fileUpload/downloadFile',
            'fileUpload/disabledFile',

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
        4 => [],
        5 => [],
        6 => [],
        7 => [],
        8 => []
    ];
}
