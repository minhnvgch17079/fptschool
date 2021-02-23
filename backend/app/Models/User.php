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
}
