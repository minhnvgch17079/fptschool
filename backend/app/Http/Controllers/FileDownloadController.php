<?php

namespace App\Http\Controllers;

use App\Http\Components\AuthComponent;
use App\Http\Middleware\Authentication;
use App\Models\FileUpload;

/**
 * Class FileDownloadController
 * @package App\Http\Controllers
 * @property FileUpload FileUpload
 */

class FileDownloadController extends Controller {
    public function downloadFile () {
        $idFile  = $this->request->get('id')         ?? null;
        $idUser  = null;
        $groupId = Authentication::$info['group_id'] ?? null;
        if (empty($idFile)) responseToClient('Can not get id of file download');

        if ($groupId == 3) $idUser = Authentication::$info['id'];

        $this->FileUpload = getInstance('FileUpload');
        $data             = $this->FileUpload->getFileInfoById($idFile, $idUser);

        if (empty($data)) responseToClient('No file found');

        $path = $data['file_path'];
        try {
            return response()->download(public_path('files/' . $path));
        } catch (\Exception $e) {
            logErr($e->getMessage());
        }
        responseToClient('There some things error while trying to download file');
    }

    public function disabledFile () {
        $idUser  = AuthComponent::user('id');
        $idFile  = $this->request->get('id')         ?? null;
        $groupId = Authentication::$info['group_id'] ?? null;
        if (empty($idFile)) responseToClient('Can not get id of file download');

        if ($groupId == 3) $idUser = Authentication::$info['id'];

        $this->FileUpload = getInstance('FileUpload');
        $data             = $this->FileUpload->disabledFile($idFile, $idUser);

        if (empty($data)) responseToClient('Can not disabled file');

        responseToClient('Disabled file successfully', true);
    }

    public function readPdfFile () {
        $idFile  = $this->request->get('id') ?? null;
        $idUser  = null;

        if (empty($idFile)) responseToClient('Can not get id of file for read');

        $this->FileUpload = getInstance('FileUpload');
        $data             = $this->FileUpload->getFileInfoById($idFile, $idUser);

        if (empty($data)) responseToClient('No file found');

        $fileType = explode('.', $data['name']);
        $fileType = end($fileType);
        $path     = $data['file_path'];
        $file     = public_path('files/') . $path;
        if ($fileType == 'pdf') {
            try {
                $filename = $path;

                header('Content-type: application/pdf');
                header('Content-Disposition: inline; filename="' . $filename . '"');
                header('Content-Transfer-Encoding: binary');
                header('Accept-Ranges: bytes');
                @readfile($file);
            } catch (\Exception $e) {
                logErr($e->getMessage());
            }
        }

        if ($fileType == 'docx') {
            try {
                $kv_strip_texts = '';
                $kv_texts = '';

                $zip = zip_open($file);

                if (!$zip || is_numeric($zip)) return false;


                while ($zip_entry = zip_read($zip)) {

                    if (zip_entry_open($zip, $zip_entry) == FALSE) continue;

                    if (zip_entry_name($zip_entry) != "word/document.xml") continue;

                    $kv_texts .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

                    zip_entry_close($zip_entry);
                }

                zip_close($zip);


                $kv_texts = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $kv_texts);
                $kv_texts = str_replace('</w:r></w:p>', "\r\n", $kv_texts);
                echo nl2br($kv_texts);
            } catch (\Exception $exception) {
                logErr($exception->getMessage());
            }
        }
    }

}
