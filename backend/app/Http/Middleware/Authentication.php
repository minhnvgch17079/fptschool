<?php

namespace App\Http\Middleware;

use App\Http\Components\AuthComponent;
use App\Http\Controllers\Auth\AuthByUser;
use App\Http\Controllers\Auth\AuthByGroup;

use Closure;

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
        'user/updateAvatar',
        'user/getAvatar',
        'comment/fileSubmissionComment',
        'comment/fileSubmissionGetComment',
        'chat/pushMessage'
    ];

    public static $info = null;

    public function handle($request, Closure $next)
    {
        $api      = $request->path();
        $infoUser = AuthComponent::user();
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
