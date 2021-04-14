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
            'user/getAllGroup',
            'faculty/report',
            'user/report',
        ],
        2 => [
            'fileUpload/downloadFile',
            'fileUpload/disabledFile',
            'marketing-co/getListSubmission',
            'fileUpload/readPdfFile',
            'student/getListSubmission',
            'faculty/getListActive',
            'marketing-ma/getNumContriForFaculty',
            'marketing-co/getSubmissionForFaculty',
            'marketing-co/updateTeacherStatus'
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
            'faculty/reportComment',
            'marketing-ma/getNumContriForFaculty',
            'marketing-ma/reportSubmissionNoComment',
            'marketing-ma/downloadZip',
            'marketing-ma/sendMailAlert',
            'faculty/report',
        ],
        5 => [
            'marketing-ma/reportSubmissionNoComment',
        ],
        6 => [],
        7 => [],
        8 => []
    ];
}
