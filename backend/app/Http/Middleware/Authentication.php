<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Auth\AuthByUser;
use App\Http\Controllers\Auth\AuthByGroup;

use Closure;
use function Composer\Autoload\includeFile;

class Authentication
{
    private $listPublicApiAccess = [
        'admin/login',
        'admin/logout'
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
        if (empty($_SESSION['info_user'])) responseToClient('No permission');
        if (in_array($api, $apiGroup))                  return $next($request);
        if (in_array($api, $apiUser))                   return $next($request);

        responseToClient('Bạn không có quyền truy cập');
    }
}
