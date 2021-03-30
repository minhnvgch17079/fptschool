<?php

namespace App\Http\Controllers;

use App\Models\CoordinatorFaculty;
use App\Models\FacultyUpload;

/**
 * Class MarketingManagerController
 * @package App\Http\Controllers
 * @property FacultyUpload FacultyUpload
 * @property CoordinatorFaculty CoordinatorFaculty
 */

class MarketingManagerController extends Controller {

    // todo: Number of contributions within each Faculty for each academic year.
    public function getNumContriForFaculty () {
        $this->FacultyUpload = getInstance('FacultyUpload');

        $facultyId = $this->request->get('faculty_id') ?? null;

        $data = $this->FacultyUpload->getData(null, $facultyId);

        if (!empty($data)) responseToClient('Get number of contributions for faculty success', true, $data);
        responseToClient('No contribution found for faculty');
    }

    public function reportSubmissionNoComment () {
        $this->FacultyUpload      = getInstance('FacultyUpload');
        $this->CoordinatorFaculty = getInstance('CoordinatorFaculty');

        $data = $this->FacultyUpload->getDataNoComment();

        if (empty($data)) responseToClient('No report has no comment');

        $dataReturn = [];
        foreach ($data as $datum) {
            $coordinatorCare = $this->CoordinatorFaculty->getUserCare($datum['faculty_id']);
            $countDate = countDate(date('Y-m-d'), $datum['created']);
            $datum['date_not_comment'] = $countDate;
            $datum['coordinator']      = [];
            if (!empty($coordinatorCare)) {
                foreach ($coordinatorCare as $coordinator) {
                    $datum['coordinator'][] = [
                        'coordinator_id' => $coordinator['id'],
                        'coordinator_username' => $coordinator['username'],
                        'coordinator_full_name' => $coordinator['full_name'],
                    ];
                }
            }
            $dataReturn[] = $datum;
        }

        responseToClient('Get report no comment success', true, $dataReturn);
    }
}
