<?php

namespace App\Models;

use App\Http\Middleware\Authentication;

class FacultyUpload extends BaseModel
{
    protected $table   = 'faculty_uploads';

    public function save ($data) {
        return $this->model->table($this->table)
            ->insert($data);
    }

    public function getData () {
        $data = $this->model->table($this->table)
            ->where("$this->table.created_by", Authentication::$info['id'])
            ->where("fi.created_by", Authentication::$info['id'])
            ->join('faculties as f', 'f.id', '=', "$this->table.faculty_id")
            ->join('files_upload as fi', 'fi.id', '=', "$this->table.file_upload_id")
            ->orderBy("$this->table.id", 'desc')
            ->get([
                "f.name as faculty_name",
                "fi.name as file_name",
                "fi.file_path as file_path",
                "fi.created as created",
                "$this->table.teacher_status",
                "fi.id as file_id"
            ]);

        return json_decode(json_encode($data), true);
    }
}
