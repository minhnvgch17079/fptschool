<?php

namespace App\Models;

class CoordinatorFaculty extends BaseModel {
    protected $table = 'coordinator_faculty';

    public function isExist ($userId, $facultyId) {
        return $this->model->table($this->table)
            ->where('user_id', $userId)
            ->where('faculty_id', $facultyId)
            ->first();
    }

    public function save ($dataSave) {
        return $this->model->table($this->table)
            ->insert($dataSave);
    }

    public function getUserCare ($facultyId) {
        $data = $this->model->table($this->table)
            ->join('users as u', 'u.id', '=', "$this->table.user_id")
            ->where("$this->table.faculty_id", $facultyId)
            ->get([
                "u.id",
                'u.username',
                'u.full_name',
                'u.email'
            ]);

        return json_decode(json_encode($data), true);
    }
}
