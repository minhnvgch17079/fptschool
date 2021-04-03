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

    public function getData ($userId, $facultyId) {
        $query = $this->model->table($this->table)
            ->join('faculties as f', 'f.id', '=', "$this->table.faculty_id")
            ->join('files_upload as fi', 'fi.id', '=', "$this->table.file_upload_id")
            ->where('fi.is_delete', 0);

        if (!empty($userId)) {
            $query->where("$this->table.created_by", $userId)
                ->where("fi.created_by", $userId);
        }

        if (!empty($facultyId)) $query->where('f.id', $facultyId);

        $data = $query
            ->orderBy("$this->table.id", 'desc')
            ->limit(100)
            ->get([
                "f.id as faculty_id",
                "f.name as faculty_name",
                "fi.name as file_name",
                "fi.file_path as file_path",
                "fi.created as created",
                "$this->table.teacher_status",
                "fi.id as file_id",
                "$this->table.group_comment_id",
                "$this->table.id as faculty_upload_id",
            ]);

        return json_decode(json_encode($data), true);
    }

    public function getDataForCoordinator ($facultyId) {
        $query = $this->model->table($this->table)
            ->join('faculties as f', 'f.id', '=', "$this->table.faculty_id")
            ->join('files_upload as fi', 'fi.id', '=', "$this->table.file_upload_id")
            ->join('coordinator_faculty as cf', 'cf.faculty_id', '=', "$this->table.faculty_id")
            ->where('fi.is_delete', 0)
            ->where('cf.user_id', Authentication::$info['id']);

        if (!empty($facultyId)) $query->where('f.id', $facultyId);

        $data = $query
            ->orderBy("$this->table.id", 'desc')
            ->limit(100)
            ->get([
                "f.id as faculty_id",
                "f.name as faculty_name",
                "fi.name as file_name",
                "fi.file_path as file_path",
                "fi.created as created",
                "$this->table.teacher_status",
                "fi.id as file_id",
                "$this->table.group_comment_id",
                "$this->table.id as faculty_upload_id",
            ]);

        return json_decode(json_encode($data), true);
    }

    public function getDataNoComment () {
        $query = $this->model->table($this->table)
            ->join('faculties as f', 'f.id', '=', "$this->table.faculty_id")
            ->join('files_upload as fi', 'fi.id', '=', "$this->table.file_upload_id")
            ->where('fi.is_delete', 0)
            ->whereNull("$this->table.group_comment_id");


        $data = $query
            ->orderBy("$this->table.id", 'desc')
            ->get([
                "f.id as faculty_id",
                "f.name as faculty_name",
                "fi.name as file_name",
                "fi.file_path as file_path",
                "fi.created as created",
                "$this->table.teacher_status",
                "fi.id as file_id",
                "$this->table.group_comment_id",
                "$this->table.id as faculty_upload_id",
            ]);

        return json_decode(json_encode($data), true);
    }

    public function updateById ($dataUpdate, $id) {
        return $this->model->table($this->table)
            ->where('id', $id)
            ->update($dataUpdate);
    }

    public function getOwnFileByGroupId ($groupId) {
        $data = $this->model->table($this->table)
            ->where('created_by', Authentication::$info['id'])
            ->where('group_comment_id', $groupId)
            ->first();

        return (array)$data;
    }

    public function countUpload ($facultyId) {
        return $this->model->table($this->table)
            ->where('faculty_id', $facultyId)
            ->where('is_active', 1)
            ->count('id');
    }

    public function getGroupByFacultyId ($facultyId) {
        $data = $this->model->table($this->table)
            ->where('faculty_id', $facultyId)
            ->get([
                'group_comment_id'
            ]);

        if (empty($data)) return null;

        $dataReturn = [];

        foreach ($data as $datum) {
            if (empty($datum->group_comment_id)) continue;
            $dataReturn[$datum->group_comment_id] = $datum->group_comment_id;
        }

        return $dataReturn;
    }
}
