<?php

namespace App\Http\Components;

use Illuminate\Support\Facades\Redis;
session_start();
class AuthComponent {

    public static function user ($key = null) {
        try {
            $phpSessionId = session_id();
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
            $phpSessionId = session_id();
            Redis::set($phpSessionId, json_encode($infoUser));
            return true;
        } catch (\Exception $exception) {
        }
        return false;
    }

    public static function setUserLogout () {
        try {
            $phpSessionId = session_id();
            Redis::del($phpSessionId);
            return true;
        } catch (\Exception $exception) {
        }
        return false;
    }
}
