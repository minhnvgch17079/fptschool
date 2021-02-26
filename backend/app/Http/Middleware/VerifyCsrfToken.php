<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $addHttpCookie = true;

    protected $except = [
        'user/login',
        'user/logout',
        'user/register',
        'admin/add',
        'admin/store',
        'admin/editdlosuredate'
    ];
}
