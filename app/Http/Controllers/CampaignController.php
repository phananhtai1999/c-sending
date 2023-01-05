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
     * @param CampaignRequest $request
     * @return JsonResponse
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
