<?php

namespace App\Http\Controllers;

use App\Models\ClosureConfigs;
use App\Models\Faculty;
use App\Models\FacultyUpload;

/**
 * @property Faculty Faculty
 * @property ClosureConfigs ClosureConfigs
 * @property FacultyUpload FacultyUpload
 */


class FacultiesController extends Controller {
    public function createFaculty () {
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
            'name'              => $facultyName,
            'description'       => $facultyDescription,
            'closure_config_id' => $closureConfigsId
        ];

        $result    = $this->Faculty->insertData($dataSave);

        if ($result) responseToClient('Create success', true, $dataSave);
        responseToClient('Create failed');
    }

    public function updateFaculty () {
        // missing only admin can access
        $id                 = $this->request->post('id')                ?? null;
        $facultyName        = $this->request->post('name')              ?? null;
        $facultyDescription = $this->request->post('description')       ?? null;
        $closureConfigsId   = $this->request->post('closure_config_id') ?? null;
        $this->ClosureConfigs = getInstance('ClosureConfigs');
        $isExistClosureId = $this->ClosureConfigs->isExistClosureConfigId($closureConfigsId);
        if (empty($isExistClosureId))         responseToClient('Closure config is not exist');
        if (empty($facultyName))              responseToClient('Invalid faculty name');
        if (empty($closureConfigsId))         responseToClient('Invalid closure config id');

        $this->Faculty = getInstance('Faculty');
        $isExist       = $this->ClosureConfigs->isExist($facultyName);

        if (!empty($isExist)) responseToClient('faculty name is exist');

        $dataSave  = [
            'name' => $facultyName,
            'description' => $facultyDescription,
            'closure_config_id' => $closureConfigsId
        ];

        $result    = $this->Faculty->updateDataById($id, $dataSave);


        if ($result) responseToClient('Update success', true);
        responseToClient('Update failed');
    }

    public function deleteFaculty($id){
        $id = $this->request->get('id') ?? null;

        if (empty($id)) responseToClient('Invalid id for delete');

        $dataSave  = ['is_delete' => 1];
        $result    = $this->Faculty->updateDataById($id, $dataSave);

        if ($result) responseToClient('Update success', true);
        responseToClient('Update failed');
    }

    public function getListActive () {
        $this->Faculty = getInstance('Faculty');
        $result        = $this->Faculty->getAll(date('Y-m-d'));

        if (empty($result)) responseToClient('There no faculty active now');
        responseToClient('Get list faculty success', true, $result);
    }

    public function report () {
        $this->Faculty       = getInstance('Faculty');
        $this->FacultyUpload = getInstance('FacultyUpload');
        $closureId           = $this->request->get('closure_id') ?? null;

        $allFaculty    = $this->Faculty->getAll(null, null, $closureId);

        if (empty($allFaculty)) responseToClient('Invalid faculty or no submission for faculty');

        $dataReport    = null;

        if (!empty($allFaculty)) {
            $dataReport['total_faculty'] = count($allFaculty);

            foreach ($allFaculty as $faculty) {
                $numberSubmission = $this->FacultyUpload->countUpload($faculty['faculty_id']);
                $dataReport['detail'][$faculty['faculty_name']] = $numberSubmission;
            }

        }

        if (empty($dataReport)) responseToClient('No data report found');

        responseToClient('Get report faculty success', true, $dataReport);
    }

}
