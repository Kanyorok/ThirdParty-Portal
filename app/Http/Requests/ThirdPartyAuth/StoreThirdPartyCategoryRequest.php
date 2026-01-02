<?php

namespace App\Http\Requests\ThirdPartyAuth;

use Illuminate\Foundation\Http\FormRequest;

class StoreThirdPartyCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->guard('sanctum')->user();
        return $user !== null && $user->thirdParty !== null;
    }

    public function rules(): array
    {
        return [
            'ThirdPartyId' => 'required|integer|exists:t_ThirdParties,Id',
            'CategoryID' => 'required|integer|exists:t_CodeDetails,Id',
        ];
    }
}
