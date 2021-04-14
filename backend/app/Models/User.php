<?php

namespace App\Models;

class User extends \App\Models\BaseModel {
    protected $table = 'users';

    public function getDataByUsername ($username) {
        $data = $this->model->table($this->table)
            ->where('username', '=', $username)
            ->first();

        return json_decode(json_encode($data), true);
    }

    public function insertData ($data) {
        return $this->model->table($this->table)->insert($data);
    }

    public function isExist ($username) {
        $data = $this->model->table($this->table)
            ->where('username', '=', $username)
            ->first();

        return (array)$data;
    }

    public function getData ($username, $fullName, $email, $phone, $groupId, $limit = 100) {
        $query = $this->model->table($this->table)
            ->where('is_active', 1)
            ->join('groups as g', 'g.id', '=', "$this->table.group_id");

        if (!empty($username))  $query->where('username', 'like', "%$username%");
        if (!empty($phone))     $query->where('phone_number', 'like', "%$phone%");
        if (!empty($fullName))  $query->where('full_name', 'like', "%$fullName%");
        if (!empty($email))     $query->where('email', '=', "%$email%");
        if (!empty($groupId))   $query->where('group_id', '=', $groupId);

        if (!empty($limit)) $query->limit($limit);

        $data = $query->get([
            "g.name as group_name",
            "$this->table.id", 'username', 'full_name', 'phone_number', 'email', 'last_change_password', "$this->table.created",
            "$this->table.created_by",
            "$this->table.modified", "$this->table.modified_by", 'age', 'DATE_of_birth', 'is_active'
        ]);

        return json_decode(json_encode($data), true);
    }

    public function updateById ($dataUpdate, $id) {
        return $this->model->table($this->table)
            ->where('id', '=', $id)
            ->update($dataUpdate);
    }

    public function isAddFaculty ($userId) {
        $data = $this->model->table($this->table)
            ->where('id', $userId)
            ->whereIn('group_id', [2, 3, 5])
            ->first();

        return (array)$data;
    }
}
