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
            ->limit(500)
            ->orderBy('id', 'desc')
            ->get();

        if (empty($data)) return null;

        $dataReturn = [];

        foreach ($data as $datum) {
            $date = date('Y-m-d', strtotime($datum->created));
            $dataReturn[$date][] = [
                'id' => $datum->id,
                'error' => $datum->error,
                'created' => $datum->created,
                'status' => $datum->status,
                'date' => $date
            ];

        }

        return $dataReturn;
    }
}
