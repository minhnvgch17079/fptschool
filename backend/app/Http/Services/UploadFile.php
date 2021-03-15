<?php

namespace App\Http\Services;

class UploadFile {

    public function uploadSingleFile ($file) {
        $fileName = $file->getClientOriginalName();
        pd($fileName);
        try {
            $result = $file->move(base_path().'/public/files/', 123);
            if (empty($result)) return false;

            $dataSave = [
                'file'
            ];
        } catch (\Exception $e) {
            logErr($e->getMessage());
        }

    }
}
