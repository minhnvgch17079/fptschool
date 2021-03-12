<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Submissions extends Model
{
    protected $connection   = 'mysql';
    protected $model        = null;

    public function __construct() {
        $this->model = DB::connection($this->connection);
    }
    protected $table    = 'submissions';

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
