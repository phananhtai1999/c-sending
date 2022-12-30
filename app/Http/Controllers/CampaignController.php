<?php

namespace App\Http\Controllers;

use App\Abstracts\AbstractRestAPIController;
use App\Http\Requests\CampaignRequest;
use App\Services\CampaignService;
use App\Services\ReceiverService;
use Illuminate\Http\Request;

class CampaignController extends AbstractRestAPIController
{
    protected $receiverService;

    public function __construct(
        CampaignService $service,
        ReceiverService $reseiverService,
    )
    {
        $this->service = $service;
        $this->receiverService = $reseiverService;
    }
    public function saveCampaign(CampaignRequest $request) {
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

        return $this->sendCreatedResponse(['data' => [
            'campaign' => $campaign,
            'receiver' => $receiver,
            ]]);
    }
}
