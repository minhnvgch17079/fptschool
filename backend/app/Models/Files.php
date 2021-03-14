<?php

namespace App\Models;


class Files extends BaseModel
{
    protected $table    = 'files';

    public $timestamps  = false;

    public function insertData ($data) {
        return $this->model->table($this->table)->insert($data);
    }


    public function isExist ($closureName) {
        $data = $this->model->table($this->table)
            ->where('name', '=', $closureName)
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
}
