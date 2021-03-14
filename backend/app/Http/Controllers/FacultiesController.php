<?php

namespace App\Http\Controllers;

use App\Models\ClosureConfigs;
use Illuminate\Http\Request;
use App\Models\Faculty;
/**
 * @property Faculty Faculty
 * @property ClosureConfigs ClosureConfigs
 */


class FacultiesController extends Controller {
    public function createFaculty () {
        // missing only admin can access
        $facultyName        = $this->request->post('name')              ?? null;
        $facultyDescription = $this->request->post('description')       ?? null;
        $closureConfigsId   = $this->request->post('closure_config_id') ?? null;
        $this->ClosureConfigs = getInstance('ClosureConfigs');
        $isExistClosureId = $this->ClosureConfigs->isExistClosureConfigId($closureConfigsId);
        if (empty($isExistClosureId))         responseToClient('Closure config is not exist');
        if (empty($facultyName))              responseToClient('Invalid faculty name');
        if (empty($closureConfigsId))         responseToClient('Invalid closure config id');

        $this->Faculty = getInstance('Faculty');
        $isExist       = $this->Faculty->isExist($facultyName);

        if (!empty($isExist)) responseToClient('faculty name is exist');


        $dataSave  = [
            'name' => $facultyName,
            'description' => $facultyDescription,
            'closure_config_id' => $closureConfigsId
        ];

        $result    = $this->Faculty->insertData($dataSave);

        if ($result) return responseToClient('Create success', true, $dataSave);
        return responseToClient('Create failed');
    }

    public function updateFaculty () {
        // missing only admin can access
        $id                = $this->request->post('id')     ?? null;
        $facultyName        = $this->request->post('name')              ?? null;
        $facultyDescription = $this->request->post('description')       ?? null;
        $closureConfigsId   = $this->request->post('closure_config_id') ?? null;
        $this->ClosureConfigs = getInstance('ClosureConfigs');
        $isExistClosureId = $this->ClosureConfigs->isExistClosureConfigId($closureConfigsId);
        if (empty($isExistClosureId))         responseToClient('Closure config is not exist');
        if (empty($facultyName))              responseToClient('Invalid faculty name');
        if (empty($closureConfigsId))         responseToClient('Invalid closure config id');

        $this->Faculty = getInstance('faculties');
        $isExist       = $this->ClosureConfigs->isExist($facultyName);

        if (!empty($isExist)) responseToClient('faculty name is exist');

        $dataSave  = [
            'name' => $facultyName,
            'description' => $facultyDescription,
            'closure_config_id' => $closureConfigsId
        ];

        $result    = $this->Faculty->updateDataById($id, $dataSave);


        if ($result) return $this->responseToClient('Update success', true);
        return $this->responseToClient('Update failed');
    }

    public function deleteFaculty($id){
        $id = $this->request->get('id') ?? null;

        if (empty($id)) responseToClient('Invalid id for delete');

        $dataSave  = ['is_delete' => 1];
        $result    = $this->Faculty->updateDataById($id, $dataSave);

        if ($result) responseToClient('Update success', true);
        responseToClient('Update failed');

    }



}
