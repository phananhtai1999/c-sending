<?php

namespace App\Services;

use App\Abstracts\AbstractService;
use App\Models\CampaignModel;
use App\Models\LinkModel;
use App\Models\ShortUrlModel;
use App\Models\VisitorModel;
use AshAllenDesign\ShortURL\Classes\Builder;
use AshAllenDesign\ShortURL\Facades\ShortURL;
use Exception;

class CampaignService extends AbstractService
{
    protected $modelClass = CampaignModel::class;

    /**
     * @param int $length
     * @return string
     */
    public function generateUrlKey(int $length = 8): string
    {
        $characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        $charactersLength = strlen($characters);
        $randomString       = "";
        for ($i = 0 ; $i < $length ; $i++) {
            $key = rand(0, $charactersLength - 1);
            $randomString .= $characters[$key];
        }

        return $randomString;
    }

    /**
     * @param $model
     * @return mixed
     */
    public function updateUrlTracking($model)
    {
        return $this->update($model, ['url' => url()->current()]);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getLinkByKey($key){
        return $this->model->where('short_link', 'like', '%' . $key)->first();
    }

    /**
     * @param $shortLink
     * @return mixed
     */
    public function getLinkByShortLink($shortLink){
        return $this->model->where('short_link', $shortLink)->first();
    }
}
