<?php

namespace App\Http\Requests\Contact;

use App\Models\BR\Client;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class AttachContactClientRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client' => ['required', 'string', 'max:200'],
        ];
    }

    public function getClient(): Client
    {
        $client = Client::where('ClientID', $this->validated('client'))->first();
        if ($client instanceof Client) {
            return $client;
        }
        throw ValidationException::withMessages([
            'client' => 'client not found, or invalid.'
        ]);
    }
}
