<?php

namespace App\Models;

class User extends \App\Models\BaseModel {
    protected $table = 'users';

    public function getDataByUsername ($username) {
        $data = $this->model
            ->where('username', '=', $username)
            ->first();

        return json_decode(json_encode($data), true);
    }

    public function insertData ($data) {
        return $this->model->insert($data);
    }

    public function isExist ($username) {
        $data = $this->model
            ->where('username', '=', $username)
            ->first();

        return (array)$data;
    }

    public function getData () {
        $data = $this->model->get([
            'id', 'username', 'full_name', 'phone_number', 'email', 'last_change_password', 'created', 'created_by',
            'modified', 'modified_by'
        ]);

        return json_decode(json_encode($data), true);
    }
}
