<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClosureConfigs;


class ClosureConfigsController extends Controller
{
    public function __construct() {
        session_start();
    }
    public function createClosureConfigs () {
        // missing only admin can access
        $closureName  = $_POST['name'] ?? null;
        $firstClosureDate  = $_POST['first_closure_DATE'] ?? null;
        $finalClosureDate  = $_POST['final_closure_DATE'] ?? null;
        $closureModel = new ClosureConfigs();

//        if (($_SESSION['username'] ?? null) != 'admin') return $this->responseToClient('No access permission');

        if (empty($closureName)) return $this->responseToClient('Invalid closure configs name');
        if (empty($firstClosureDate)) return $this->responseToClient('Invalid Closure date');
        if (empty($finalClosureDate))  return $this->responseToClient('Invalid Closure date');

        $isExist   = $closureModel->isExist($closureName);

        if (!empty($isExist))       return $this->responseToClient('Closure configs name exist');


        $dataSave  = [
            'name' => $closureName,
            'first_closure_DATE' => $firstClosureDate,
            'final_closure_DATE' => $finalClosureDate
        ];

        $result    = $closureModel->insertData($dataSave);

        if ($result) return $this->responseToClient('Register success', true);
        return $this->responseToClient('Register failed');
    }


}
