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


