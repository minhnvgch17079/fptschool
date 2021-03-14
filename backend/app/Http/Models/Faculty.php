<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Faculty extends Model
{
    protected $table = 'faculties';
    protected $connection   = 'mysql';
    protected $model        = null;
    public function __construct() {
        $this->model = DB::connection($this->connection)->table($this->table);
    }

    public $timestamps = false;
    public function insertData ($data) {
        return $this->model->insert($data);
    }

    public function isExist ($facultyName) {
        $data = $this->model
            ->where('name', '=', $facultyName)
            ->first();

        return (array)$data;
    }
}
