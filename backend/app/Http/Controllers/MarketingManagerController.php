<?php

namespace App\Http\Controllers;

use App\Models\FacultyUpload;

/**
 * Class MarketingManagerController
 * @package App\Http\Controllers
 * @property FacultyUpload FacultyUpload
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
        $this->FacultyUpload = getInstance('FacultyUpload');
        $data = $this->FacultyUpload->getDataNoComment();
        pd($data);
    }
}
