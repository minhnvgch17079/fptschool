<?php

namespace App\Http\Controllers;

use App\Models\Log;

/**
 * Class AdminController
 * @package App\Http\Controllers
 * @property Log Log
 */

class AdminController extends Controller
{
    public function getAllError () {
        $this->Log = getInstance('Log');

        $dataError = $this->Log->getAllError();

        if (!empty($dataError)) responseToClient('Get error success', true, $dataError);
        responseToClient('No error found');
    }
}
