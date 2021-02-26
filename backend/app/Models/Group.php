<?php

namespace App\Models;

class Group extends \App\Models\BaseModel {
    protected $table = 'groups';

    public function getDataByGroupName ($group_name)
    {
        $data = $this->model
            ->where('name', '=' , $group_name)
            ->first();

        return json_decode(json_encode($data), true);
    }
}
