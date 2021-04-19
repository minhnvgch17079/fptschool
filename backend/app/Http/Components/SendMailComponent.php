<?php

namespace App\Http\Components;

class SendMailComponent {
    public function sendMail ($title, $receive, $message) {
        try {
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("minhnvgch17079@fpt.edu.vn", "Greenwich University");
            $email->setSubject($title);
            $email->addTo($receive, "Min min");
            $email->addContent("text/plain", $message);
            $sendgrid = new \SendGrid(env('SEND_GRID_KEY'));
            $response = $sendgrid->send($email);
            if ($response->statusCode()) return true;
        } catch (\Exception $exception) {
            logErr($exception->getMessage());
        }
        return false;
    }
}
