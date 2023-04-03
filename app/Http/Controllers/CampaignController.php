<?php

namespace App\Http\Controllers;

use App\Abstracts\AbstractRestAPIController;
use App\Http\Requests\CampaignRequest;
use App\Services\CampaignService;
use App\Services\KafkaService;
use App\Services\ReceiverService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
     *          mediaType="application/json",
     *          @OA\Schema(
     *              required={"config", "type", "status", "paramerters", "destination", "template"},
     *              @OA\Property(property="config", type="object"),
     *              @OA\Property(property="type", type="string"),
     *              @OA\Property(property="campaign_uuid", type="integer"),
     *              @OA\Property(property="template", type="string"),
     *              @OA\Property(property="subject", type="string"),
     *              @OA\Property(property="receivers", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="uuid", type="integer"),
     *                      @OA\Property(property="parameters", type="array",
     *                          @OA\Items(
     *                              @OA\Property(property="username", type="string"),
     *                          )
     *                      ),
     *                      @OA\Property(property="destination", type="string"),
     *                  )
     *              )
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
        try {
            $campaign = $this->service->create([
                'campaign_uuid' => $request->get('campaign_uuid'),
                'template' => $request->get('template'),
                'type' => $request->get('type'),
                'status' => 'new',
                'config' => $request->get('config'),
                'subject' => $request->get('subject')
            ]);
            if ($request->get('type') == 'sms') {
                $topic = config('kafka.topic.sms.default');
                $quantityReceiver = config('kafka.quantity_receiver.sms');
            } elseif ($request->get('type') == 'email') {
                $topic = config('kafka.topic.email');
                $quantityReceiver = config('kafka.quantity_receiver.email');
            } else {
                $topic = config('kafka.topic.telegram');
                $quantityReceiver = config('kafka.quantity_receiver.telegram');
            }
            $count = 0;
            foreach ($request->get('receivers') as $receiver) {
                $renderContent = $this->renderBody($request->get('template'), $receiver['parameters']);
                $renderSubject = $this->renderBody($request->get('subject'), $receiver['parameters']);

                $receiver = $this->receiverService->create([
                    'campaign_uuid' => $campaign->_id,
                    'receiver_uuid' => $receiver['uuid'],
                    'destination' => $receiver['destination'],
                    'status' => 'new',
                    'parameters' => $receiver['parameters']
                ]);
                $receivers[] = [
                    'receiver_uuid' => $receiver->_id,
                    'subject' => $renderSubject,
                    'content' => $renderContent,
                    'destination' => $receiver['destination']
                ];
                $count++;
                $messages = [
                    'config' => $request->get('config'),
                    'receiver' => $receivers
                ];
                if ($count == $quantityReceiver) {
                    $this->kafkaService->sendNotification($topic, $messages);
                    $count = 0;
                    $receivers = [];
                }
            };
            $messages = [
                'config' => $request->get('config'),
                'receiver' => $receivers
            ];
            $this->kafkaService->sendNotification($topic, $messages);
            $campaign = $this->service->update($campaign, [
                'status' => 'active',
            ]);
            $response = $this->sendCreatedResponse(['data' => [
                'campaign' => $campaign
            ]]);
        } catch (\Exception $exception) {
            $response = $this->sendBadRequestJsonResponse(['message' => $exception->getMessage()]);
        }

        return $response;
    }

    public function renderBody($mailTemplate, $contact, $campaign = null)
    {
        $websiteName = $contact['website_name'] ?? '';
        $websiteDomain = $contact['website_domain'] ?? '';
        $websiteDescription = $contact['website_description'] ?? '';
        $contactFirstName = $contact['contact_first_name'] ?? '';
        $contactMiddleName = $contact['contact_middle_name'] ?? '';
        $contactLastName = $contact['contact_last_name'] ?? '';
        $contactPhone = $contact['contact_phone'] ?? '';
        $contactSex = $contact['contact_sex'] ?? '';
        $contactDob = $contact['contact_dob'] ?? '';
        $contactCountry = $contact['contact_country'] ?? '';
        $contactCity = $contact['contact_city'] ?? '';
        $currentDay = $contact['current_day'] ?? '';
        $currentTime = $contact['current_time'] ?? '';
        $search = [
            '{{website_name}}',
            '{{website_domain}}',
            '{{website_description}}',
            '{{contact_first_name}}',
            '{{contact_middle_name}}',
            '{{contact_last_name}}',
            '{{contact_phone}}',
            '{{contact_sex}}',
            '{{contact_dob}}',
            '{{contact_country}}',
            '{{contact_city}}',
            '{{current_day}}',
            '{{current_time}}'

        ];
        $replace = [
            $websiteName, $websiteDomain, $websiteDescription,
            $contactFirstName, $contactMiddleName, $contactLastName, $contactPhone, $contactSex, $contactDob, $contactCountry, $contactCity,
            $currentDay, $currentTime
        ];

        return Str::replace($search, $replace, $mailTemplate);
    }
}
