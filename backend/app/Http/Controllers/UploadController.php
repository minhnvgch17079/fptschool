<?php

namespace App\Http\Controllers;
use App\Models\File;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function createForm()
    {
        return view('upload');
    }

    public function fileUpload(Request $req)
    {
//        $req = validate([
//           'file' => 'require|mimes:csv,txt,xlx,xls,pdf,docx|max:2048'
//        ]);
        $fileModel = new File;

        if($req->file())
        {
            $fileName = time().'_'.$req->file->getClientOriginalName();
            $filePath = $req->file('file')->storeAs('uploads', $fileName,'public');

            $fileModel->file_name = time().'_'.$req->file->getClientOriginalName();
            $fileModel->file_path = '/storage/'. $filePath;

            $dataSave = [
                'file_name' => $fileName,
                'file_path' => $filePath
            ];

            $result  = $fileModel->save($dataSave);

            echo('insert success');
        }
    }
}
