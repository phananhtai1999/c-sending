<?php

namespace App\Services;

use App\Abstracts\AbstractService;
use App\Models\ReceiverModel;

class ReceiverService extends AbstractService
{
    protected $modelClass = ReceiverModel::class;

    public function getRecord($numberOfLast = 0) {
        return $this->model->select('receiver_uuid', 'status')->where('status','!=', 'new')->get()->slice($numberOfLast)->values();
    }
}
