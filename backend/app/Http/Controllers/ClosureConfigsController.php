<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use Illuminate\Http\Request;
use App\Models\ClosureConfigs;

/**
 * @property ClosureConfigs ClosureConfigs
 */

class ClosureConfigsController extends Controller {
    public function get () {
        $this->ClosureConfigs = getInstance('ClosureConfigs');
        $data                 = $this->ClosureConfigs->getAllData();

        if (!empty($data)) responseToClient('Get data success', true, $data);
        responseToClient('No data found');
    }

    public function create() {
        $closureName      = $this->request->post('name')       ?? null;
        $firstClosureDate = $this->request->post('first_date') ?? null;

        if (empty($closureName))              responseToClient('Invalid closure configs name');
        if (empty($firstClosureDate))         responseToClient('Invalid Closure date');

        if (!validateDate($firstClosureDate, 'Y-m-d')) responseToClient('Invalid Closure date');

        $this->ClosureConfigs = getInstance('ClosureConfigs');
        $isExist              = $this->ClosureConfigs->isExist($closureName);

        if (!empty($isExist)) responseToClient('Closure configs name is exist');

        $dataSave  = [
            'name'               => $closureName,
            'first_closure_date' => "$firstClosureDate 00:00:00",
            'final_closure_date' => date('Y-m-d 23:59:59', strtotime("+14 days", strtotime($firstClosureDate)))
        ];

        $result    = $this->ClosureConfigs->insertData($dataSave);

        if ($result) responseToClient('Create success', true, $dataSave);
        responseToClient('Create failed');
    }

    public function updateClosureConfigs () {
        $id                = $this->request->post('id')     ?? null;
        $closureName       = $this->request->post('name')   ?? null;
        $firstClosureDate  = $this->request->post('first_closure_date') ?? null;
        $finalClosureDate  = $this->request->post('final_closure_date') ?? null;

        if (empty($closureName))              responseToClient('Invalid closure configs name');
        if (empty($firstClosureDate))         responseToClient('Invalid Closure date');
        if (empty($finalClosureDate))         responseToClient('Invalid Closure date');
        if (!validateDate($firstClosureDate)) responseToClient('Invalid Closure date');
        if (!validateDate($finalClosureDate)) responseToClient('Invalid Closure date');
        if (countDate($firstClosureDate, $finalClosureDate) != 14) responseToClient('Only 14 days accepted');

        $this->ClosureConfigs = getInstance('ClosureConfigs');
        $closureModelUpdate   = $this->ClosureConfigs->getDataById($id);
        $isExist              = $this->ClosureConfigs->isExist($closureName);

        if (empty($closureModelUpdate)) responseToClient('Invalid closure config');
        if (!empty($isExist))           responseToClient('Closure configs name is exist');

        $dataSave  = [
            'name'               => $closureName,
            'first_closure_date' => $firstClosureDate,
            'final_closure_date' => $finalClosureDate
        ];

        $result  = $this->ClosureConfigs->updateDataById($id, $dataSave);

        if ($result) responseToClient('Update success', true);
        responseToClient('Update failed');
    }

    public function deleteClosureConfigs() {
        $id = $this->request->get('id') ?? null;

        if (empty($id)) responseToClient('Invalid id for delete');

        $dataSave  = ['is_delete' => 1];
        $result    = $this->ClosureConfigs->updateDataById($id, $dataSave);

        if ($result) responseToClient('Update success', true);
        responseToClient('Update failed');
    }
}
