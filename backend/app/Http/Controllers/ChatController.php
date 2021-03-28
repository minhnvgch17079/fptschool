<?php

namespace App\Http\Controllers;

class ChatController extends Controller {
    public function pushMessage () {
        $message = $this->request->get('message') ?? null;

        if (empty($message)) responseToClient('Please input message');

        $options = array(
            'cluster' => 'ap1',
            'useTLS' => true
        );
        $pusher = new \Pusher\Pusher(
            '23bf5cf1702a1b1b0d49',
            'f432a44af5c2714fd141',
            '1178223',
            $options
        );

        $data['message'] = 'hello world';
        $pusher->trigger('my-channel', 'my-event', $data);

        responseToClient('ok');
    }
}
