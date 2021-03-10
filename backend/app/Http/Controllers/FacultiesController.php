<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faculty;


class FacultiesController extends Controller {
    public function createFaculty () {
        // missing only admin can access
        $facultyName = $_POST['name'] ?? null;
        $facultyDescription = $_POST['description'] ?? null;
        $closureConfigsId  = $_POST['closure_config_id'] ?? null;
        $facultyModel = new Faculty();

//        if (($_SESSION['username'] ?? null) != 'admin') return $this->responseToClient('No access permission');

        if (empty($facultyName)) return $this->responseToClient('Invalid faculty name');
        if (empty($closureConfigsId))  return $this->responseToClient('Invalid Closure date');

        $isExist   = $facultyModel->isExist($facultyName);

        if (!empty($isExist))       return $this->responseToClient('Faculty name is exist');


        $dataSave  = [
            'name' => $facultyName,
            'description' => $facultyDescription,
            'closure_config_id' => $closureConfigsId
        ];

        $result    = $facultyModel->insertData($dataSave);

        if ($result) return $this->responseToClient('Create success', true);
        return $this->responseToClient('Create failed');
    }

    public function updateFaculty ($id , Request $req) {
        // missing only admin can access
        $facultyModelUpdate = Faculty::find($id);

        $facultyName  = $req->name;
        $facultyDescription  = $req->description;
        $closureConfigsId  = $req->closure_config_id;

        $facultyModelUpdate->name = $facultyName;
        $facultyModelUpdate->description = $facultyDescription;
        $facultyModelUpdate->closure_config_id = $closureConfigsId;


//        if (($_SESSION['username'] ?? null) != 'admin') return $this->responseToClient('No access permission');
        $closureModel = new Faculty();

        $isExist   = $closureModel->isExist($facultyName);

        if (!empty($isExist))       return $this->responseToClient('Closure configs name is exist');

        $result  = $facultyModelUpdate->save();


        if ($result) return $this->responseToClient('Update success', true);
        return $this->responseToClient('Update failed');
    }

    public function deleteFaculty($id){
       $result = Faculty::destroy($id);

        if ($result) return $this->responseToClient('Delete success', true);
        return $this->responseToClient('Delete failed');

    }



}
