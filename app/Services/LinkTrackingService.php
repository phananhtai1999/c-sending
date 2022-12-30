<?php

namespace App\Services;

use App\Abstracts\AbstractService;
use App\Models\LinkModel;
use App\Models\LinkTrackingModel;
use App\Models\ShortUrlModel;
use App\Models\VisitorModel;
use AshAllenDesign\ShortURL\Classes\Builder;
use AshAllenDesign\ShortURL\Facades\ShortURL;
use Exception;

class LinkTrackingService extends AbstractService
{
    protected $modelClass = LinkTrackingModel::class;

}
