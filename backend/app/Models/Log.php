<?php

namespace App\Models;

class Log extends BaseModel {
    protected $table = 'logs';

    public function saveError ($message) {
        return $this->model->table($this->table)
            ->insert(['error' => $message]);
    }
}
