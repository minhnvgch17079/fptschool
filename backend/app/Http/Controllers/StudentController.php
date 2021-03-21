<?php

namespace App\Http\Controllers;
use App\Http\Services\UploadFile;
use App\Http\Components\StudentComponent;
class StudentController extends Controller {
    public function uploadAssignment() {
        $files = $this->request->file('files') ?? null;

        if (empty($files)) responseToClient('File upload required');

        $studentComponent = new StudentComponent();
        $validateFile     = $studentComponent->validateFileUpload($files);

        if (!empty($validateFile)) responseToClient($validateFile);

        $upload = new UploadFile();
        $result = true;

        foreach ($files as $file) {
            $result = $upload->uploadSingleFile($file);
            pd($result);
        }
    }

}
