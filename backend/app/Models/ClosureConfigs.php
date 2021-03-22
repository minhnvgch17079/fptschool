<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class ClosureConfigs extends BaseModel
{
    protected $table    = 'closure_configs';

    public $timestamps  = false;

    public function insertData ($data) {
        return $this->model->table($this->table)->insert($data);
    }


    public function isExist ($closureName, $id = null) {
        $data = $this->model->table($this->table)
            ->where('name', '=', $closureName);

        if (!empty($id)) $data->where('id', '<>', $id);

        $data = $data->first();

        return (array)$data;
    }

    public function isExistClosureConfigId ($closureConfigId) {
        $data = $this->model->table($this->table)
            ->where('id', '=', $closureConfigId)
            ->first();

        return (array)$data;
    }

    public function getDataById ($id) {
        $data = $this->model->table($this->table)
            ->where('id', '=', $id)
            ->first();

        return (array)$data;
    }

    public function updateDataById ($id, $dataUpdate) {
        return $this->model->table($this->table)
            ->where('id', '=', $id)
            ->update($dataUpdate);
    }

    public function getAllData () {
        $data = $this->model->table($this->table)
            ->where('is_delete', 0)
            ->get();

        return json_decode(json_encode($data), true);
    }
}
