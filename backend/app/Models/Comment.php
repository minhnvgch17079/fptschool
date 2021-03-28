<?php

namespace App\Models;

class Comment extends BaseModel
{
    public $table = 'comments';

    public function getMaxGroupId () {
        $data = $this->model->table($this->table)
            ->max('group_id');

        return $data;
    }

    public function save ($dataSave) {
        return $this->model->table($this->table)
            ->insert($dataSave);
    }

    public function getDataByGroup ($groupId) {
        $data = $this->model->table($this->table)
            ->where('group_id', $groupId)
            ->get();

        return json_decode(json_encode($data), true);
    }

    public function countMessageByListGroup ($groupId) {
        return $this->model->table($this->table)
            ->whereIn('group_id', $groupId)
            ->count();

    }
}
