<?php

namespace App\Http\Controllers;

use App\Models\FacultyUpload;

/**
 * Class MarketingCoordinatorController
 * @package App\Http\Controllers
 * @property FacultyUpload FacultyUpload
 */

class MarketingCoordinatorController extends Controller {
   public function getListSubmission () {
       $this->FacultyUpload = getInstance('FacultyUpload');

       $facultyId = $this->request->get('faculty_id') ?? null;

       $data = $this->FacultyUpload->getData(null, $facultyId);

       if (empty($data)) responseToClient('No file submission uploaded');
       responseToClient('Get list submission uploaded success', true, $data);
   }
}
