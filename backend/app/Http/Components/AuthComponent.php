<?php

namespace App\Http\Components;

use Illuminate\Support\Facades\Redis;

class AuthComponent {

    public static function user ($key = null) {
        try {
            $phpSessionId = $_COOKIE["laravel_session"];
            $infoUser     = Redis::get($phpSessionId);
            $infoUser     = json_decode($infoUser, true);
            if (!empty($key)) return $infoUser[$key] ?? null;
            return $infoUser;
        } catch (\Exception $exception) {
        }
        return null;
    }

    public static function setUserLogin ($infoUser) {
        try {
            $phpSessionId = $_COOKIE["laravel_session"];
            Redis::set($phpSessionId, json_encode($infoUser));
            return true;
        } catch (\Exception $exception) {
        }
        return false;
    }

    public static function setUserLogout () {
        try {
            $phpSessionId = $_COOKIE["laravel_session"];
            Redis::del($phpSessionId);
            return true;
        } catch (\Exception $exception) {
        }
        return false;
    }
}
