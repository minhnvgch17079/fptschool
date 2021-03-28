<?php

namespace App\Models;

class Group extends BaseModel
{
    protected $table = 'groups';

    public static $MARKETING_MANAGER     = 4;
    public static $MARKETING_COORDINATOR = 2;
    public static $ADMIN    = 1;
    public static $STUDENT  = 3;

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

    public function getAllGroup () {
        $data = $this->model->table($this->table)
            ->get();

        return json_decode(json_encode($data), true);
    }
}
