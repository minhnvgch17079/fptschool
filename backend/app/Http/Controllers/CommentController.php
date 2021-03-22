<?php

namespace App\Http\Controllers;

use App\Http\Middleware\Authentication;
use App\Models\Comment;
use App\Models\FacultyUpload;

/**
 * Class CommentController
 * @package App\Http\Controllers
 * @property Comment Comment
 * @property FacultyUpload FacultyUpload
 */

class CommentController extends Controller {
    public function fileSubmissionComment () {
        $groupId = $this->request->post('group_id')           ?? null;
        $content = $this->request->post('content')            ?? null;
        $facultyUploadId  = $this->request->post('faculty_upload_id')  ?? null;

        if (empty($facultyUploadId))  responseToClient('Missing faculty for comment');
        if (empty($content)) responseToClient('Please input comment');

        $this->Comment       = getInstance('Comment');
        $this->FacultyUpload = getInstance('FacultyUpload');

        if (empty($groupId)) {
            $flag = true;
            $maxGroupComment = $this->Comment->getMaxGroupId();
            if (empty($maxGroupComment)) $maxGroupComment = 0;
            $groupId = $maxGroupComment + 1;
        }

        $dataSave = [
            'group_id'          => $groupId,
            'message'           => $content,
            'created_by'        => Authentication::$info['id'],
            'username_created'  => Authentication::$info['username']
        ];

        $this->Comment->model->beginTransaction();
        $result = $this->Comment->save($dataSave);


        if (empty($result)) {
            $this->Comment->model->rollBack();
            responseToClient('Comment failed');
        }

        if (!empty($flag)) {
            $result = $this->FacultyUpload->updateById([
                'group_comment_id' => $groupId
            ], $facultyUploadId);
        }


        if ($result) {
            $this->Comment->model->commit();
            responseToClient('Comment success', true, $dataSave);
        }
        $this->Comment->model->rollBack();
        responseToClient('Comment failed');
    }

    public function fileSubmissionGetComment () {
        $groupId = $this->request->get('group_id');

        $this->Comment = getInstance('Comment');

        if (empty($groupId)) responseToClient('No group comment found');

        if (Authentication::$info['group_id'] == 3) {
            $this->FacultyUpload = getInstance('FacultyUpload');
            $isOwn = $this->FacultyUpload->getOwnFileByGroupId($groupId);

            if (empty($isOwn)) responseToClient('No permission for read comment');
        }

        $data = $this->Comment->getDataByGroup($groupId);

        if (empty($data)) responseToClient('No comment found');
        responseToClient('Get list comment success', true, $data);
    }
}
