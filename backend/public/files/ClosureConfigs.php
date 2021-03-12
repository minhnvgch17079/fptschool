<?php

namespace App\Models;

class ClosureConfigs extends \App\Models\BaseModel
{
    protected $table = 'closure_configs';
    public function insertData ($data) {
        return $this->model->insert($data);
    }

    public function isExist ($closureName) {
        $data = $this->model
            ->where('name', '=', $closureName)
            ->first();

        return (array)$data;
    }
}
