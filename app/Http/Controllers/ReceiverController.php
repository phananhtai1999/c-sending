<?php

namespace App\Http\Controllers;

use App\Abstracts\AbstractRestAPIController;
use App\Http\Requests\ProcessedReceiverRequest;
use App\Http\Requests\UpdateStatusReceiverRequest;
use App\Services\ReceiverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

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


    /**
     * @OA\Get (
     *     path="/api/processed-receivers",
     *     description="get processed receiver",
     *     operationId="receiver",
     *     tags={"receiver"},
     *     @OA\Parameter (name="get_all",in="path"),
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *         @OA\JsonContent(
     *              @OA\Property(property="code", type="int"),
     *              @OA\Property(property="status", type="string"),
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="processed_receiver", type="array",
     *                          @OA\Items(
     *                              @OA\Property(property="_id", type="string"),
     *                              @OA\Property(property="campaign_uuid", type="int"),
     *                              @OA\Property(property="destination", type="string"),
     *                              @OA\Property(property="status", type="string"),
     *                              @OA\Property(property="parameters", type="array",
     *                                  @OA\Items(
     *                                      @OA\Property(property="contact_first_name", type="string"),
     *                                      @OA\Property(property="contact_middle_name", type="string"),
     *                                      @OA\Property(property="contact_last_name", type="string"),
     *                                      @OA\Property(property="contact_phone", type="integer"),
     *                                  )
     *                              ),
     *                              @OA\Property(property="created_at", type="string"),
     *                              @OA\Property(property="updated_at", type="string"),
     *                          )
     *                      )
     *                  )
     *              )
     *          )
     *     )
     * )
     */
    public function processedReceivers(ProcessedReceiverRequest $request) {
        if ($request->get_all) {
            if (!empty($request->get('filter')['campaign_uuid'])) {
                $processedReceivers = $this->service->findAllWhere([
                    'status' => 'done',
                    'campaign_uuid' => (int)$request->get('filter')['campaign_uuid']
                ]);
            } else {
                $processedReceivers = $this->service->findAllWhere(['status' => 'done']);
            }
        } else {
            $redis = Redis::connection();
            $numberOfLast = $redis->get('number_of_last') ?? 0;
            $processedReceivers = $this->service->getRecord($numberOfLast);
            $redis->set('number_of_last', $numberOfLast + count($processedReceivers));
        }

        return $this->sendSuccessResponse(['data' => ['processed_receiver' => $processedReceivers]], 'Successfully');
    }
}
