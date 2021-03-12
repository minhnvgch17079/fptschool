<?php


namespace App\Http\Controllers;
use App\Models\Files;
use App\Models\Submissions;
use Illuminate\Support\Facades\DB;

/**
 * @property Submissions Submissions
 * @property Files Files
 */



class SubmissionsController extends Controller
{
    public function submitMagazines()
    {
        $facultyId = $this->request->post('faculty_id') ?? null;
        $files = $this->request->file('files') ?? null;
        $data = DB::table('faculties')->join('closure_configs','faculties.closure_config_id','=','closure_configs.id')->where('faculties.id',$facultyId)->orderBy('faculties.id','desc')->get();
        $first_closure_date = $data[0]->first_closure_DATE;
        $now = date('Y-m-d H:i:s');
        if($now > $first_closure_date) responseToClient('The submission time has expired');
        if (empty($facultyId))         responseToClient('Invalid faculty id'); //them validate check faculty co ton tai hay khong
        if (empty($files))             responseToClient('Invalid file');

        $submissions = new Submissions();
        $submissions->faculty_id = $facultyId;
        $submissions->save();
        $submissions_id = $submissions->id;

        foreach ($files as $file) {
            $result = $file->move(base_path() . '/public/files/', $file->getClientOriginalName());
            $this->Files = getInstance('Files');
            $dataFile = [
                'file_name' => $file->getClientOriginalName(),
                'submissions_id' => $submissions_id,
                'file_path' => $result
            ];
            $this->Files->insertData($dataFile);
        }
    }


}
