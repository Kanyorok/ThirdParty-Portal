<?php

namespace App\Http\Requests\Call;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StartCallNewContactRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'call_initiated' => [
                                     'required',
                                     'date_format:"H:i"',
                                    ],
                'Phone' => [
                                     'required',
                                     'string',
                                     'regex: /^[(2541)(2547)(01)(07)]+[0-9]{9}$/i',
                                    ],
                'Name' => [
                                     'required',
                                     'string',
                                     'max:255',
                                    ],
               ];
    }

    /**
     * @throws ValidationException
     */
    public function getStart(): Carbon
    {
        $current_start = Carbon::createFromFormat('H:i', $this->validated('call_initiated'));
        if (! $current_start instanceof Carbon) {
            throw ValidationException::withMessages(['call_initiated' => 'invalid date format']);
        }
        if ($current_start->greaterThan(now())) {
            $current_start = now()->subMinute();
        }

        return $current_start;
    }
}
