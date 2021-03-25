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
}
