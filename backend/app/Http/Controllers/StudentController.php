<?php

namespace App\Http\Controllers;
use App\Http\Middleware\Authentication;
use App\Http\Services\UploadFile;
use App\Http\Components\StudentComponent;
use App\Models\FacultyUpload;


/**
 * Class StudentController
 * @package App\Http\Controllers
 * @property FacultyUpload FacultyUpload
 */

class StudentController extends Controller {
    public function uploadAssignment() {
        $files      = $this->request->file('files')      ?? null;
        $idFaculty  = $this->request->post('id_faculty') ?? null;
        $note       = $this->request->post('note')       ?? null;

        if (empty($files))      responseToClient('File upload required');
        if (empty($idFaculty))  responseToClient('Invalid id faculty');

        $studentComponent = new StudentComponent();
        $validateFile     = $studentComponent->validateFileUpload($files);
        $validateFaculty  = $studentComponent->validateFaculty($idFaculty);

        if (!empty($validateFaculty)) responseToClient($validateFaculty);
        if (!empty($validateFile))    responseToClient($validateFile);

        $upload = new UploadFile();
        $this->FacultyUpload = getInstance('FacultyUpload');

        foreach ($files as $file) {
            $idUploadFile = $upload->uploadSingleFile($file);

            if (empty($idUploadFile)) responseToClient('Failed to upload file ' .  $file->getClientOriginalName());

            $result = $this->FacultyUpload->save([
                'is_active'      => 1,
                'note'           => $note,
                'file_upload_id' => $idUploadFile,
                'faculty_id'     => $idFaculty,
                'created_by'     => Authentication::$info['id'] ?? null
            ]);

            if (empty($result)) responseToClient('Failed to upload file ' .  $file->getClientOriginalName());
        }

        // gui mail

        responseToClient('Upload success', true);
    }

    public function getListSubmission () {
        $this->FacultyUpload = getInstance('FacultyUpload');
        $facultyId           = $this->request->get('faculty_id') ?? null;

        $data = $this->FacultyUpload->getData(Authentication::$info['id'], $facultyId);

        if (empty($data)) responseToClient('No file submission uploaded');
        responseToClient('Get list submission uploaded success', true, $data);
    }

}
