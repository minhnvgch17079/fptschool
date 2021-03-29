<?php

namespace App\Http\Controllers;

use App\Http\Components\AuthComponent;
use App\Models\CoordinatorFaculty;
use App\Models\FacultyUpload;

/**
 * Class MarketingCoordinatorController
 * @package App\Http\Controllers
 * @property FacultyUpload FacultyUpload
 * @property CoordinatorFaculty CoordinatorFaculty
 */

class MarketingCoordinatorController extends Controller {
   public function getListSubmission () {
       $this->FacultyUpload = getInstance('FacultyUpload');

       $facultyId = $this->request->get('faculty_id') ?? null;

       $data = $this->FacultyUpload->getData(null, $facultyId);

       if (empty($data)) responseToClient('No file submission uploaded');
       responseToClient('Get list submission uploaded success', true, $data);
   }

    public function getSubmissionForFaculty () {
        $this->FacultyUpload = getInstance('FacultyUpload');

        $facultyId = $this->request->get('faculty_id') ?? null;

        $this->CoordinatorFaculty = getInstance('CoordinatorFaculty');
        $isExist = $this->CoordinatorFaculty->isExist(AuthComponent::user('id'), $facultyId);

        if (empty($isExist)) responseToClient('No permission for this faculty');


        $data = $this->FacultyUpload->getData(null, $facultyId);

        if (!empty($data)) responseToClient('Get number of contributions for faculty success', true, $data);
        responseToClient('No contribution found for faculty');
    }

    public function updateTeacherStatus () {
       $status          = $this->request->get('status') ?? null;
       $facultyUploadId = $this->request->get('faculty_upload_id') ?? null;

       if (empty($facultyUploadId)) responseToClient('Invalid faculty upload id');

       if (!in_array($status, ['rejected', 'accepted'])) responseToClient('Invalid status');

       $this->FacultyUpload = getInstance('FacultyUpload');

       $dataUpdate = [
           'teacher_status' => $status
       ];

       $result = $this->FacultyUpload->updateById($dataUpdate, $facultyUploadId);

       if ($result) responseToClient('Update status success', true, $dataUpdate);

       responseToClient('Update status failed');
    }
}
