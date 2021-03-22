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

    public function getData ($username, $fullName, $email, $phone, $groupId) {
        $query = $this->model->table($this->table)
            ->where('is_active', 1);

        if (!empty($username))  $query->where('username', '=', $username);
        if (!empty($phone))     $query->where('phone_number', '=', $phone);
        if (!empty($fullName))  $query->where('full_name', 'like', "%$fullName%");
        if (!empty($email))     $query->where('email', '=', $email);
        if (!empty($groupId))   $query->where('group_id', '=', $groupId);

        $query->limit(100);

        $data = $query->get([
            'id', 'username', 'full_name', 'phone_number', 'email', 'last_change_password', 'created', 'created_by',
            'modified', 'modified_by', 'age', 'DATE_of_birth'
        ]);

        return json_decode(json_encode($data), true);
    }

    public function updateById ($dataUpdate, $id) {
        return $this->model->table($this->table)
            ->where('id', '=', $id)
            ->update($dataUpdate);
    }
}
