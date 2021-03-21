<?php

namespace App\Http\Services;

use App\Models\FileUpload;
use function GuzzleHttp\Psr7\str;

/**
 * Class UploadFile
 * @package App\Http\Services
 * @property FileUpload FileUpload
 */

class UploadFile {
    private $ruleNameLength = 200;
    private $ruleFileSize   = 5228618;

    public function uploadSingleFile ($file, $fileName = null) {
        $fileNameDisplay = $fileName ?? $file->getClientOriginalName();
        $endType         = explode('.', $fileNameDisplay);
        $fileNameSave    = randomString(50) . "." .last($endType);
        $pathSave        = base_path().'/public/files/';
        $fileSize        = $file->getSize();

        if (strlen($fileNameDisplay) > $this->ruleNameLength) {
            logErr("Class: " . __CLASS__ . " function: " . __FUNCTION__ . " line: " . __LINE__ . " error: File Name $fileNameDisplay must small than 200 characters" );
            return false;
        }

        if ($fileSize > $this->ruleFileSize) {
            logErr("Class: " . __CLASS__ . " function: " . __FUNCTION__ . " line: " . __LINE__ . " error: File to large, must smaller than 4,5 mb" );
            return false;
        }

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
            if ($result) return $result;
            return false;
        } catch (\Exception $e) {
            logErr("Class: " . __CLASS__ . "-function: " . __FUNCTION__ . "-line: " . __LINE__ . "-error: " . $e->getMessage());
        }
        return false;
    }
}
