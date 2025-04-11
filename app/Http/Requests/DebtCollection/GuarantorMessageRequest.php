<?php

namespace App\Http\Requests\DebtCollection;

use App\Http\Requests\Base\MessageRequest;
use App\Models\BR\Client;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GuarantorMessageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'guarantor_message_content' => [
                                                'required_without:message_to',
                                                'string',
                                               ],
                'message_to'                => [
                                                'required_without:guarantor_message_content',
                                                'min:9',
                                                'max:50',
                                               ],
                'message_content'           => [
                                                'required_without:guarantor_message_content',
                                                'string',
                                                'min:5',
                                               ],
               ];
    }

    public function messages(): array
    {
        return [
                'guarantor_message_content.required_without' => 'message content is required',
                'guarantor_message_content.min'              => 'write a longer message',
                'message_to.required_without'                => 'message to is required',
                'message_content.required_without'           => 'message content is required',
                'message_content.min'                        => 'write a longer message',
               ];
    }

    public function getClientPhone(Client $client): string
    {
        return (new MessageRequest())->getClientPhone($client, $this->validated('message_to'));
    }
}
