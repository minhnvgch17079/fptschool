<?php

namespace App\Models;

class Log extends BaseModel {
    protected $table = 'logs';

    public function saveError ($message) {
        return $this->model->table($this->table)
            ->insert(['error' => $message]);
    }

    public function getAllError () {
        $data = $this->model->table($this->table)
            ->limit(100)
            ->orderBy('id', 'desc')
            ->get();

        return json_decode(json_encode($data), true);
    }
}
