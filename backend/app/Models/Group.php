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

    public function isExist ($groupName) {
        return $this->model->table($this->table)
            ->where('name', $groupName)
            ->first();
    }

    public function insertData($data) {
        return $this->model->table($this->table)
            ->insert($data);
    }

    public function findById ($id) {
        return $this->model->table($this->table)
            ->where('id', $id)->first();
    }

    public function updateById ($dataUpdate, $id) {
        return $this->model->table($this->table)
            ->where('id', $id)
            ->update($dataUpdate);
    }

    public function get ($name) {
        $query = $this->model->table($this->table);

        if (!empty($name)) $query->where('name', 'like', "%$name%");

        return $query->get();
    }
}
