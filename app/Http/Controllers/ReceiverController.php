<?php

namespace App\Http\Controllers;

use App\Abstracts\AbstractRestAPIController;
use App\Http\Requests\UpdateStatusReceiverRequest;
use App\Services\ReceiverService;
use Illuminate\Http\Request;

class ReceiverController extends AbstractRestAPIController
{
    public function __construct(ReceiverService $service)
    {
        $this->service = $service;
    }

    public function updateStatus(UpdateStatusReceiverRequest $request) {
        $model = $this->service->findOneById($request->get('receiver_id'));
        $result = $this->service->update($model, ['status' => $request->get('status')]);
        if ($result) {

            return $this->sendSuccessResponse([], 'Update successfully');
        }
    }
}
