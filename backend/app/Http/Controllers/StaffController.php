<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StaffController extends Controller
{

    public function login () {
        if (1 == 2) return $this->responseToClient('Error method');
        if (2 == 3) return $this->responseToClient('ahahaha');
        if (3 == 3) return $this->responseToClient('true', true, 'right');
    }

    public function logout () {
        $dataReturn = [
            'success' => false,
            'message' => 'Error method',
            'data'    => []
        ];

        return json_encode($dataReturn);
    }

    public function test3 () {
        $dataReturn = [
            'success' => false,
            'message' => 'Error method',
            'data'    => []
        ];

        return json_encode($dataReturn);
    }

    public function test4 () {
        $dataReturn = [
            'success' => false,
            'message' => 'Error method',
            'data'    => []
        ];

        return json_encode($dataReturn);
    }
}
