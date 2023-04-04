<?php

namespace App\Services;

use App\Abstracts\AbstractService;
use App\Models\ReceiverModel;

class ReceiverService extends AbstractService
{
    protected $modelClass = ReceiverModel::class;

    public function getRecord($numberOfLast = 0) {
        return $this->model->where('status', 'done')->skip($numberOfLast)->orderBy('updated_at', 'ASC')->get();
    }
}
