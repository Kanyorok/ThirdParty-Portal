<?php

namespace App\Http\Requests\Schedule;

use Illuminate\Foundation\Http\FormRequest;

class PartyNoteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'party_note' => [
                                 'required',
                                 'string',
                                 'max:5000',
                                ],
               ];
    }

    public function getPartyNote(): string
    {
        return $this->validated('party_note');
    }
}
