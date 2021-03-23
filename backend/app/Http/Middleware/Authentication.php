<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Auth\AuthByUser;
use App\Http\Controllers\Auth\AuthByGroup;

use Closure;
use function Composer\Autoload\includeFile;

class Authentication
{
    private $listPublicApiAccess = [
        'user/login',
        'user/logout'
    ];
    private $listAccessAllLogin = [
        'user/getInfoUser',
        'user/changePassword',
        'user/updateProfile',
        'comment/fileSubmissionComment',
        'comment/fileSubmissionGetComment'
    ];

    public static $info = null;

    public function handle($request, Closure $next)
    {
        return $next($request);
        $api      = $request->path();
        $infoUser = session('info_user', null);
        $groupId  = $infoUser['group_id'] ?? null;
        $username = $infoUser['username'] ?? null;
        $apiGroup = AuthByGroup::$groups[$groupId] ?? [];
        $apiUser  = AuthByUser::$users[$username]  ?? [];

        if (in_array($api, $this->listPublicApiAccess)) return $next($request);
        if (empty($infoUser)) responseToClient('No permission. Please login first');
        self::$info = $infoUser;

        if (in_array($api, $this->listAccessAllLogin))  return $next($request);

        // admin access all
        if ($groupId == 1) return $next($request);
        // access by rule
        if (in_array($api, $apiGroup))                  return $next($request);
        if (in_array($api, $apiUser))                   return $next($request);

        responseToClient('No permission');
    }
}
