<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class BaseModel extends DB {
    protected $connection   = 'production';
    protected $table        = null;
    protected $model        = null;

    public function __construct() {
        $this->model = DB::connection($this->connection);
    }
}
