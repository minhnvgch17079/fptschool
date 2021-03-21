<?php

namespace App\Http\Controllers\Auth;

class AuthByGroup {
    public static $groups = [
        1 => [],
        2 => [],
        3 => [
            'student/uploadAssignment',
            'faculty/getListActive',
            'student/getListSubmission'
        ],
        4 => [],
        5 => [],
        6 => [],
        7 => [],
        8 => []
    ];
}
