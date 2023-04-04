<?php

namespace App\Services;

use App\Abstracts\AbstractService;
use App\Models\ReceiverModel;

class ReceiverService extends AbstractService
{
    protected $modelClass = ReceiverModel::class;

    public function getRecord($numberOfLast = 0, $campaignUuid = false) {
        if (!$campaignUuid) {
            return $this->model->where('status', '!=', 'new')->skip($numberOfLast)->orderBy('updated_at', 'ASC')->get();
        }
        return $this->model->where('status', '!=', 'new')
            ->where('campaign_uuid', (int)$campaignUuid)->skip($numberOfLast)->orderBy('updated_at', 'ASC')->get();
    }
}
