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

    /**
     * @OA\Post(
     *     path="/api/update-status",
     *     description="update status",
     *     operationId="update",
     *     tags={"receiver"},
     *     @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *          mediaType="multiPart/form-data",
     *          @OA\Schema(
     *              required={"receiver_id","status"},
     *              @OA\Property(property="receiver_id", type="string"),
     *              @OA\Property(property="status", type="string"),
     *          )
     *      )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *         @OA\JsonContent(
     *              @OA\Property(property="code", type="int"),
     *              @OA\Property(property="status", type="string"),
     *              @OA\Property(property="message", type="string"),
     *          )
     *     )
     * )
     */
    public function updateStatus(UpdateStatusReceiverRequest $request) {
        $model = $this->service->findOneById($request->get('receiver_id'));
        $result = $this->service->update($model, ['status' => $request->get('status')]);
        if ($result) {

            return $this->sendSuccessResponse([], 'Update successfully');
        }
    }
}
