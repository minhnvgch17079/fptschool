<?php
namespace App\Http\Components;
class StudentComponent {
    public function validateFileUpload ($files) {
        $maxFileNum     = 4;
        $ruleFileSize   = 5228618;
        $acceptFile     = [
            'docx',
            'pdf'
        ];

        if (count($files) > $maxFileNum) return 'Maximum 4 files upload in one time';

        foreach ($files as  $file) {
            if ($file->getSize() > $ruleFileSize) return 'File to large, must smaller than 4,5 mb';

            $fileName    = $file->getClientOriginalName();
            $fileExplode = explode('.', $fileName);
            $fileType    = end($fileExplode);

            if (!in_array($fileType, $acceptFile)) return 'Only pdf or docx accepted';
        }

        return false;
    }
}
