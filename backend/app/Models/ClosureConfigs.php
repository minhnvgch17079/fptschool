<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ClosureConfigs extends Model
{
    protected $table = 'closure_configs';
    protected $connection   = 'mysql';
    protected $model        = null;

    public function __construct() {
        $this->model = DB::connection($this->connection)->table($this->table);
    }

    public $timestamps = false;

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
