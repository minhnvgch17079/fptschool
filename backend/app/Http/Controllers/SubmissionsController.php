<?php


namespace App\Http\Controllers;
use App\Models\Faculty;
use App\Models\Files;
use App\Models\Submissions;
use Illuminate\Support\Facades\DB;

/**
 * @property Submissions Submissions
 * @property Files Files
 * @property Faculty Faculty

 */



class SubmissionsController extends Controller
{
    public function getSubmissionData () {
        $submissionId = $this->request->post('id');
        $this->Submissions = getInstance('Submissions');
        $data                 = $this->Submissions->getSubmissionData($submissionId);

        if (!empty($data)) responseToClient('Get data success', true, $data);
        responseToClient('No data found');
    }

    public function submitMagazines()
    {
        $userId = $_SESSION['info_user']['id'];
        $facultyId = $this->request->post('faculty_id') ?? null;
        $files     = $this->request->file('files') ?? null;

        $this->Faculty     = getInstance('Faculty');
        $this->Files       = getInstance('Files');
        $this->Submissions = getInstance('Submissions');

        $data = $this->Faculty->getClosureConfig($facultyId);

        $first_closure_date = $data['first_closure_DATE'];
        $now  = date('Y-m-d H:i:s');

        $isExist = $this->Faculty->isExistFacultyId($facultyId);

        if($now > $first_closure_date) responseToClient('The submission time has expired');
        if (empty($facultyId))         responseToClient('Invalid faculty id'); //them validate check faculty co ton tai hay khong
        if (empty($isExist))           responseToClient('faculty is not exist');
        if (empty($files))             responseToClient('Invalid file');

        $dataSave   = [
            'faculty_id' => $facultyId,
            'created_by' => $userId
        ];
        $idInserted = $this->Submissions->insertGetId($dataSave);

        foreach ($files as $file) {
            $result = $file->move(base_path() . '/public/files/', $file->getClientOriginalName());
            $dataFile = [
                'file_name'      => $file->getClientOriginalName(),
                'submissions_id' => $idInserted,
                'file_path'      => $result,
                'created_by' => $userId
            ];
            $this->Files->insertData($dataFile);
        }

        responseToClient('Success', true);
    }
}
