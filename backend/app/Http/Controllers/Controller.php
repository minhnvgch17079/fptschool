<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
//    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

//    public function __construct()
//    {
//        header('Access-Control-Allow-Origin', '*');
//        header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
//    }

    protected function responseToClient ($message, $success = false, $data = []) {
        return json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ]);
    }
}
