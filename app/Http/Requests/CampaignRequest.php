<?php

namespace App\Http\Requests;

use App\Enums\CampaignType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\CampaignStatus;

class CampaignRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'config' => ['required'],
            'template' => ['required'],
            'type' => ['required', new Enum(CampaignType::class)],
            'status' => ['required', new Enum(CampaignStatus::class)],
            'parameters' => ['required'],
        ];
    }
}
