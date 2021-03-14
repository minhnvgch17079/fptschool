<?php

if (!function_exists('pd')) {
    function pd($content)
    {
        echo '<pre>';
        print_r($content);
        echo '</pre>';
        die();
    }
}

if (!function_exists('pr')) {
    function pr($content)
    {
        echo '<pre>';
        print_r($content);
        echo '</pre>';
    }
}

if (!function_exists('getInstance')) {
    function getInstance($modelName)
    {
        if (!empty(model::$instanceCreated[$modelName])) return model::$instanceCreated[$modelName];
        return model::init($modelName);
    }
}

if (!(function_exists('getValueMapToTable'))) {
    function getValueMapToTable($value, $tableQuota, $type = 'default')
    {
        $result = null;
        if (empty($tableQuota)) return null;
        if ($type == 'more') {
            foreach ($tableQuota as $w => $c) {
                if ((float)$value > (float)$w) {
                    $result = $c;
                } else {
                    break;
                }
            }
        } else {
            foreach ($tableQuota as $w => $c) {
                if ((float)$value >= (float)$w) {
                    $result = $c;
                } else {
                    break;
                }
            }
        }
        return $result;
    }
}

if (!(function_exists('responseToClient'))) {
    function responseToClient($message = 'Error method', $success = false, $data = []) {
        header("Content-type: application/json", true);
        $dataResponse = [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ];

        echo json_encode($dataResponse);

        try {
            if (ob_get_length() !== false) {
                @ob_end_flush();
            }
        } catch (Exception $e) {
        }

        die();
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


