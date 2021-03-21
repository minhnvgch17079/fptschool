<?php

namespace App\Models;

class FileUpload extends BaseModel
{
    protected $table = 'files_upload';

    public function save ($dataSave) {
        return $this->model->table($this->table)
            ->insertGetId($dataSave);
    }
}
