<?php

namespace App\Http\Requests\ThirdPartyAuth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\ThirdPartyStatusEnum;

class UpdateThirdPartyStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // TODO: Add policy for authorization 
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ThirdPartyStatusEnum::class)],
        ];
    }
}
