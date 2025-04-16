<?php

namespace App\Http\Requests\DebtCollection;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoanCampaignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'Label'   => [
                              'required',
                              'string',
                              'max:200',
                             ],
                'Content' => [
                              'required',
                              'string',
                              'min:5',
                              'max:50000',
                             ],
                'Notes'   => [
                              'nullable',
                              'string',
                              'max:5000',
                             ],
               ];
    }

    public function getMessageContent(): string
    {
        return $this->string('Content')->remove(["\r", "\n", "\t", "\0", "\x0B"])->replace("\u{A0}", " ")->toString();
    }
}
