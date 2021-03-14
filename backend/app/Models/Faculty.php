<?php

namespace App\Models;

class Faculty extends BaseModel
{
    protected $table = 'faculties';
    protected $model = null;

    public $timestamps = false;
    public function insertData ($data) {
        return $this->model->table($this->table)->insert($data);
    }

    public function isExist ($facultyName) {
        $data = $this->model->table($this->table)
            ->where('name', '=', $facultyName)
            ->first();

        return (array)$data;
    }
}
