<?php

namespace App\Models;

class Faculty extends BaseModel
{
    protected $table   = 'faculties';
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
    public function isExistFacultyId ($facultyId) {
        $data = $this->model->table($this->table)
            ->where('id', '=', $facultyId)
            ->first();

        return (array)$data;
    }

    public function getDataById ($id) {
        $data = $this->model->table($this->table)
            ->where('id', '=', $id)
            ->first();

        return (array)$data;
    }

    public function updateDataById ($id, $dataUpdate) {
        return $this->model->table($this->table)
            ->where('id', '=', $id)
            ->update($dataUpdate);
    }

    public function getClosureConfig ($id) {
        $data = $this->model->table($this->table)
            ->join('closure_configs as c', "$this->table.closure_config_id", '=', 'c.id')
            ->where("$this->table.id", $id)->orderBy('faculties.id','desc')->first();

        return (array)$data;
    }
}
