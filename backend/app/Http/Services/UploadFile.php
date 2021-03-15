<?php

namespace App\Http\Services;

use App\Models\FileUpload;

/**
 * Class UploadFile
 * @package App\Http\Services
 * @property FileUpload FileUpload
 */

class UploadFile {

    public function uploadSingleFile ($file, $fileName = null) {
        $fileNameDisplay = $fileName ?? $file->getClientOriginalName();
        $endType         = explode('.', $fileNameDisplay);
        $fileNameSave    = randomString(50) . "." .last($endType);
        $pathSave        = base_path().'/public/files/';

        $this->FileUpload = getInstance('FileUpload');

        try {
            $result = $file->move($pathSave, $fileNameSave);
            if (empty($result)) return false;

            $dataSave = [
                'name'      => $fileNameDisplay,
                'is_delete' => 0,
                'file_path' => $pathSave
            ];

            $result = $this->FileUpload->save($dataSave);
            if ($result) return true;
            return false;
        } catch (\Exception $e) {
            logErr($e->getMessage());
        }
    }
}
