<?php

namespace App\Http\Requests\DMS;

use App\Models\Auth\User;
use App\Models\DMS\DMSSignature;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ApproveDocumentValidationRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'Signature' => ['required', 'string', 'max:200']
        ];
    }

    public function getSignature(User $user): DMSSignature
    {
        $sign = DMSSignature::query()->user($user)->where('SignatureId', $this->string('Signature'))->first();
        if (!$sign instanceof DMSSignature) {
            throw ValidationException::withMessages(['Signature' => 'Invalid signature provided']);
        }
        return $sign;
    }
}
