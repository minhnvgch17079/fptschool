<?php

namespace App\Http\Controllers;

use App\Models\Group;
use function GuzzleHttp\Psr7\str;


/**
 * Class UserController
 * @package App\Http\Controllers
 * @property Group Group
 */


class GroupController extends Controller {
    public function __construct($request)
    {
        parent::__construct($request);
    }

    public function add () {
        $name = $this->request->post('name') ?? 'admin';
        $desc = $this->request->post('desc') ?? 'Group admin';

        if (strlen($name) > 50)  responseToClient('Group name must smaller than 100 characters');
        if (strlen($desc) > 500) responseToClient('Desc must smaller than 500 characters');

        $this->Group = getInstance('Group');
        $isExist     = $this->Group->isExist($name);

        if (!empty($isExist)) responseToClient('Group exist');

        $dataSave = [
            'name'        => $name,
            'description' => $desc
        ];

        $result = $this->Group->insertData($dataSave);

        if ($result) responseToClient('Create group success', true);
        responseToClient('Failed to create group');
    }

    public function editGroup () {
        $id = $this->request->post('id') ?? null;
        $name = $this->request->post('name') ?? null;
        $desc = $this->request->post('desc') ?? null;

        if (empty($id))          responseToClient('Invalid id group');
        if (empty($name))        responseToClient('Name is required');
        if (strlen($name) > 50)  responseToClient('Group name must smaller than 100 characters');
        if (strlen($desc) > 500) responseToClient('Desc must smaller than 500 characters');

        $this->Group = getInstance('Group');
        $checkGroup = $this->Group->findById($id);

        if (empty($checkGroup)) responseToClient('Group does not exist');

        $dataSave = [
            'name'        => $name,
            'description' => $desc
        ];

        $result = $this->Group->updateById($dataSave, $id);

        if ($result) responseToClient('Update success group', true);
        responseToClient('Failed to update group');
    }

    public function get () {
        logErr('test');
        $name = $this->request->get('name') ?? null;

        $this->Group = getInstance('Group');
        $data = $this->Group->get($name);

        if (!empty($data)) responseToClient('Get list groups success', true, $data);
        responseToClient('No data group found');
    }
}
