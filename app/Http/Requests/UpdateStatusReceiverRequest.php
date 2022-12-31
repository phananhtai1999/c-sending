<?php

namespace App\Http\Requests;

use App\Enums\ReceiverStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateStatusReceiverRequest extends FormRequest
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
            'receiver_id' => ['required'],
            'status' => ['required', new Enum(ReceiverStatus::class)],
        ];
    }
}
