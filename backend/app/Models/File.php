<?php

namespace App\Models;

class File extends \App\Models\BaseModel {
    protected $table = 'files';

    public function getClientOriginalName($file_name)
    {
        $data = $this->model
            ->where('file_name', '=', $file_name)
            ->first();

        return json_decode(json_encode($data), true);
    }
    public function save ($data) {
        return $this->model->insert($data);
    }
}
