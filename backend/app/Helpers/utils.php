<?php


if (!(function_exists('pd'))) {
    function pd ($content) {
        print_r('');
        print_r($content);
        print_r('');
        die;
    }
}

if (!(function_exists('responseToClient'))) {
    function responseToClient ($message, $success = false, $data = []) {
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data
        ]);
        die;
    }
}

if (!(function_exists('validateDate'))) {
    function validateDate($date, $format = 'Y-m-d H:i:s') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}

if (!(function_exists('countDate'))) {
    function countDate($date_1 , $date_2 , $differenceFormat = '%a' ) {
        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);

        $interval = date_diff($datetime1, $datetime2);

        return $interval->format($differenceFormat);

    }
}

if (!function_exists('getInstance')) {
    function getInstance($modelName)
    {
        if (!empty(model::$instanceCreated[$modelName])) return model::$instanceCreated[$modelName];
        return model::init($modelName);
    }
}




