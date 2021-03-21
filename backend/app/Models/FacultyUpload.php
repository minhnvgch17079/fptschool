<?php

namespace App\Models;

class FacultyUpload extends BaseModel
{
    protected $table   = 'faculty_uploads';

    public function save ($data) {
        return $this->model->table($this->table)
            ->insert($data);
    }

    public function getData () {
        $data = $this->model->table($this->table)
            ->join('faculties as f', 'f.id', '=', "$this->table.faculty_id")
            ->join('files_upload as fi', 'fi.id', '=', "$this->table.file_upload_id")
            ->orderBy("$this->table.id", 'desc')
            ->get();

        return json_decode(json_encode($data), true);
    }
}
