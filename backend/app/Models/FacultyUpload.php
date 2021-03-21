<?php

namespace App\Models;

class FacultyUpload extends BaseModel
{
    protected $table   = 'faculty_uploads';

    public function save ($data) {
        return $this->model->table($this->table)
            ->insert($data);
    }
}
