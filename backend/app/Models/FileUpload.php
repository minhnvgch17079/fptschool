<?php

namespace App\Models;

class FileUpload extends BaseModel
{
    protected $table = 'files_upload';

    public function save ($dataSave) {
        return $this->model->table($this->table)
            ->insertGetId($dataSave);
    }

    public function getFileInfoById ($id, $userId) {
        $query = $this->model->table($this->table)
            ->where('id', $id)
            ->where('is_delete', 0);

        if (!empty($userId)) $query->where('created_by', $userId);

        $data = $query->first();

        return (array)$data;
    }

    public function disabledFile ($id, $userId) {
        $query = $this->model->table($this->table)
            ->where('id', $id)
            ->where('is_delete', 0);

        if (!empty($userId)) $query->where('created_by', $userId);

        return $query->update([
            'is_delete' => 1
        ]);
    }
}
