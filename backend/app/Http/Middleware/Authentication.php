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

    public function handle($request, Closure $next)
    {
        $api      = $request->path();
        $infoUser = session('info_user', null);
        $groupId  = $infoUser['group_id'] ?? null;
        $username = $infoUser['username'] ?? null;
        $apiGroup = AuthByGroup::$groups[$groupId] ?? [];
        $apiUser  = AuthByUser::$users[$username]  ?? [];

        if (in_array($api, $this->listPublicApiAccess)) return $next($request);
        if (empty(session()->get('info_user'))) responseToClient('No permission. Please login first');
        return $next($request);
//        if (in_array($api, $apiGroup))                  return $next($request);
//        if (in_array($api, $apiUser))                   return $next($request);

        responseToClient('No permission');
    }
}
