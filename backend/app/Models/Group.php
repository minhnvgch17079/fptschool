<?php

namespace App\Models;

class Group extends \App\Models\BaseModel
{
    protected $table = 'groups';
    public function getDataByGrouprname ($group_name) {
        $data = $this->model->table($this->table)
            ->where('group_name', '=', $group_name)
            ->first();

        return json_decode(json_encode($data), true);
    }
}
