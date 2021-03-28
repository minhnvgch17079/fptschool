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

    public function getAll ($date, $id = null, $closureId = null) {
        $query = $this->model->table($this->table)
            ->join('closure_configs as c', "$this->table.closure_config_id", '=', "c.id");

        if (!empty($date)) {
            $query->where('c.first_closure_DATE', '<=', $date)
                ->where('c.final_closure_DATE', '>=', $date);
        }

        if (!empty($id)) $query->where("$this->table.id", $id);
        if (!empty($closureId)) $query->where("c.id", $closureId);

        $data = $query->get([
            'c.first_closure_DATE as first_closure_DATE',
            'c.final_closure_DATE as final_closure_DATE',
            'c.name as closure_name',
            "$this->table.id as faculty_id",
            "$this->table.name as faculty_name",
            "$this->table.description as faculty_description",
            "c.id as closure_id"
        ]);
        return json_decode(json_encode($data), true);
    }
}
