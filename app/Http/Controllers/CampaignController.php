<?php

namespace App\Http\Controllers;

use App\Abstracts\AbstractRestAPIController;
use App\Http\Requests\CampaignRequest;
use App\Services\CampaignService;
use App\Services\KafkaService;
use App\Services\ReceiverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignController extends AbstractRestAPIController
{
    /**
     * @OA\Info(
     *      version="1.0.0",
     *      title="Laravel C-sending",
     *      description="L5 Swagger OpenApi description",
     *      @OA\License(
     *          name="Apache 2.0",
     *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
     *      )
     * )
     *
     * @OA\Server(
     *      url="http://127.0.0.1:8000",
     *      description="API Server C-sending"
     * )
     */
    protected ReceiverService $receiverService;
    protected KafkaService $kafkaService;

    public function __construct(
        CampaignService $service,
        ReceiverService $receiverService,
        KafkaService    $kafkaService,
    )
    {
        $this->service = $service;
        $this->receiverService = $receiverService;
        $this->kafkaService = $kafkaService;
    }

    /**
     * @OA\Post(
     *     path="/api/campaign",
     *     description="save sending data",
     *     operationId="store",
     *     tags={"Campaign"},
     *     @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *          mediaType="multiPart/form-data",
     *          @OA\Schema(
     *              required={"config", "type", "status", "paramerters", "destination", "template"},
     *              @OA\Property(property="config", type="object"),
     *              @OA\Property(property="type", type="string"),
     *              @OA\Property(property="status", type="string"),
     *              @OA\Property(property="paramerters", type="object"),
     *              @OA\Property(property="destination", type="string"),
     *              @OA\Property(property="template", type="string"),
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
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="campaign", type="array",
     *                          @OA\Items(
     *                              @OA\Property(property="_id", type="string"),
     *                              @OA\Property(property="template", type="string"),
     *                              @OA\Property(property="status", type="string"),
     *                              @OA\Property(property="type", type="string"),
     *                              @OA\Property(property="config", type="object"),
     *                              @OA\Property(property="created_at", type="string"),
     *                          )
     *                      ),
     *                      @OA\Property(property="receiver", type="array",
     *                          @OA\Items(
     *                              @OA\Property(property="_id", type="int"),
     *                              @OA\Property(property="campaign_uuid", type="int"),
     *                              @OA\Property(property="destination", type="int"),
     *                              @OA\Property(property="status", type="int"),
     *                              @OA\Property(property="parameters", type="int"),
     *                              @OA\Property(property="created_at", type="string"),
     *                          )
     *                      ),
     *                  )
     *              )
     *          )
     *     )
     * )
     */

    public function saveCampaign(CampaignRequest $request)
    {
        $campaign = $this->service->create([
            'template' => $request->get('template'),
            'type' => $request->get('type'),
            'status' => 'new',
            'config' => $request->get('config')
        ]);

        $receiver = $this->receiverService->create([
            'campaign_uuid' => $campaign->_id,
            'destination' => $request->get('destination'),
            'status' => 'new',
            'parameters' => $request->get('parameters')
        ]);
        if ($request->get('type') == 'sms') {
            $topic = config('kafka.topic.sms.default');
        } elseif ($request->get('type') == 'email') {
            $topic = config('kafka.topic.email');
        } else {
            $topic = config('kafka.topic.telegram');
        }
        $this->kafkaService->sendNotification($topic, $receiver);
        return $this->sendCreatedResponse(['data' => [
            'campaign' => $campaign,
            'receiver' => $receiver,
        ]]);
    }
}
