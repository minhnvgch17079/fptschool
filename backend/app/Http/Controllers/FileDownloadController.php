<?php

namespace App\Http\Controllers;

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
        return response()->download(public_path($path));
    }

}
