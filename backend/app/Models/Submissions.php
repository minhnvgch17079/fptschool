<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Submissions extends Model
{
    protected $connection   = 'production';
    protected $table    = 'submissions';
    protected $model        = null;

    public function __construct() {
        $this->model = DB::connection($this->connection);
    }

    public $timestamps  = false;

    public function insertData ($data) {
        return $this->model->table($this->table)->insert($data);
    }


    public function isExist ($facultyId) {
        $data = $this->model->table($this->table)
            ->where('id', '=', $facultyId)
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

    public function insertGetId ($dataSave) {
        return $this->model->table($this->table)
            ->insertGetId($dataSave);
    }

    public function getSubmissionData ($id) {
        $data = $this->model->table($this->table)
            ->join('files as f', "$this->table.id", '=', 'f.submissions_id')
            ->join('users as u', 'f.created_by', '=' , 'u.id')
            ->where("$this->table.id", $id)->orderBy('f.id','desc')->get();

        return (array)$data;
    }
}
