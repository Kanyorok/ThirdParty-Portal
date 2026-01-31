<?php

namespace App\Http\Requests\Base;

use App\Models\Auth\User;
use App\Models\BR\Client;
use App\Models\CRM\Lead;
use App\Models\ThirdParies\Board;
use App\Services\BR\ClientService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class MessageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'message_to' => [
                                      'required',
                                      'min:9',
                                      'max:50',
                                     ],
                'message_content' => [
                                      'required',
                                      'string',
                                      'min:5',
                                     ],
               ];
    }

    public function messages(): array
    {
        return ['message_content.min' => 'Write something about it.'];
    }

    /**
     * @throws ValidationException
     */
    public function getLeadPhone(Lead $lead): string
    {
        $phone = $this->validated('message_to');

        if ($lead->Phone === $phone) {
            return $phone;
        }

        //check contact.
        if ($lead->contacts()->where('t_Contacts.Phone', $phone)->exists()) {
            return $phone;
        }

        throw ValidationException::withMessages(['message_to' => 'The phone number does not related to lead.']);
    }

    public function getBoardMemberPhone(Board $boardMember): string
    {
        $phone = $this->validated('message_to');

        if ($boardMember->Phone === $phone) {
            return $phone;
        }

        throw ValidationException::withMessages(['message_to' => 'The phone number does not related to board member.']);
    }

    public function getUserPhone(User $user): string
    {
        $phone = $this->validated('message_to');

        if ($user->Phone === $phone) {
            return $phone;
        }

        throw ValidationException::withMessages(['message_to' => 'The phone number does not related to user.']);
    }

    /**
     * @throws ValidationException
     */
    public function getClientPhone(Client $client, string $message_to = null): string
    {
        $phone = $message_to ?? $this->validated('message_to');

        if ((new ClientService($client))->phoneNo() === $phone) {
            return $phone;
        }

        //check contact.
        if ($client->contacts()->where('t_Contacts.Phone', $phone)->exists()) {
            return $phone;
        }

        throw ValidationException::withMessages(['message_to' => 'The phone number does not related to client.']);
    }
}
