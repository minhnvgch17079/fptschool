<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Group;
use App\Models\ClosureConfigs;
use Illuminate\Support\Facades\Hash;


class ClosureConfigsController extends Controller
{
    public function createClosureConfigs () {
        // missing only admin can access
        $closureName  = $_POST['closureName'] ?? null;
        $firstClosureDate  = $_POST['firstClosureDate'] ?? null;
        $finalClosureDate  = $_POST['finalClosureDate'] ?? null;
        $closureModel = new ClosureConfigs();

        if (($_SESSION['username'] ?? null) != 'admin') return $this->responseToClient('No access permission');

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
