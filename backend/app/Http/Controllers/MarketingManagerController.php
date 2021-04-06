<?php

namespace App\Http\Controllers;

use App\Models\CoordinatorFaculty;
use App\Models\FacultyUpload;
use Illuminate\Support\Facades\Mail;

/**
 * Class MarketingManagerController
 * @package App\Http\Controllers
 * @property FacultyUpload FacultyUpload
 * @property CoordinatorFaculty CoordinatorFaculty
 */

class MarketingManagerController extends Controller {

    // todo: Number of contributions within each Faculty for each academic year.
    public function getNumContriForFaculty () {
        $this->FacultyUpload = getInstance('FacultyUpload');

        $facultyId = $this->request->get('faculty_id') ?? null;

        $data = $this->FacultyUpload->getDataForCoordinator($facultyId);

        if (!empty($data)) responseToClient('Get number of contributions for faculty success', true, $data);
        responseToClient('No contribution found for faculty');
    }

    public function reportSubmissionNoComment () {
        $this->FacultyUpload      = getInstance('FacultyUpload');
        $this->CoordinatorFaculty = getInstance('CoordinatorFaculty');

        $data = $this->FacultyUpload->getDataNoComment();

        if (empty($data)) responseToClient('No report has no comment');

        $dataReturn = [];
        foreach ($data as $datum) {
            $coordinatorCare = $this->CoordinatorFaculty->getUserCare($datum['faculty_id']);
            $countDate = countDate(date('Y-m-d'), $datum['created']);
            $datum['date_not_comment'] = $countDate;
            $datum['coordinator']      = [];
            if (!empty($coordinatorCare)) {
                foreach ($coordinatorCare as $coordinator) {
                    $datum['coordinator'][] = [
                        'coordinator_id'        => $coordinator['id'],
                        'coordinator_username'  => $coordinator['username'],
                        'coordinator_full_name' => $coordinator['full_name'],
                        'coordinator_email'     => $coordinator['email'],
                    ];
                }
            }
            if ($countDate < 14) $dataReturn['success'][] = $datum;
            else $dataReturn['exception'][] = $datum;
        }

        responseToClient('Get report no comment success', true, $dataReturn);
    }

    public function sendMailAlert () {
        $dataSubmission = $this->request->post('data_submission') ?? null;
        $dataUserCare   = $this->request->post('data_user_care')  ?? null;

        if (empty($dataSubmission)) responseToClient('No data submission found');
        if (empty($dataUserCare))   responseToClient('No data user care found');

        $dataUserCare['coordinator_email'] = 'minhnvgch17079@fpt.edu.vn';

        $title = "Faculty: " . $dataSubmission['faculty_name'] . " with submission name: " . $dataSubmission['file_name'] . " was not comment in " . $dataSubmission['date_not_comment'] . " days";
        $body = 'The submission has no comment in ' . $dataSubmission['date_not_comment'] . ' days. Please check it and comment';

        try {
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("minhnvgch17079@fpt.edu.vn", "Greenwich University");
            $email->setSubject($title);
            $email->addTo($dataUserCare['coordinator_email'], "Min min");
            $email->addContent("text/plain", $body);
            $sendgrid = new \SendGrid('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');
            $response = $sendgrid->send($email);
            if ($response->statusCode()) responseToClient('Sent mail success', true);
        } catch (\Exception $exception) {
            logErr($exception->getMessage());
        }
        responseToClient('Send mail failed');
    }
}
