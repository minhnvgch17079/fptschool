<?php


class model {
    public static $instanceCreated = [];

    public static function init ($modelName) {
        $modelCreate = "App\Models\\$modelName";
        $model       = new $modelCreate();

        self::$instanceCreated[$modelName] = $model;

        return $model;
    }
}
