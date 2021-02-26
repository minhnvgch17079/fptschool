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
    public function getDataByGroupId ($group_id)
    {
        $data = $this->model
            ->where('group_id', '=', $group_id)
            ->first();
        return (array)$data;
    }
    public function showData($data)
    {

    }

}
