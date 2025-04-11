<?php

namespace App\Http\Requests\Lead;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class IndividualLeadRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'first_name'   => [
                                   'required',
                                   'string',
                                   'max:250',
                                  ],
                'surname'      => [
                                   'required',
                                   'string',
                                   'max:150',
                                  ],
                'phone'        => [
                                   'required',
                                   'string',
                                   'max:15',
                                   'unique:App\Models\Lead,Phone',
                                  ],
                'email'        => [
                                   'nullable',
                                   'email:rfc,dns',
                                   'max:250',
                                   'unique:App\Models\Lead,Email',
                                  ],
                'job_title'    => [
                                   'nullable',
                                   'string',
                                   'max:200',
                                  ],
                'last_contact' => [
                                   'nullable',
                                   'date_format:"Y-m-d H:i"',
                                   'before:now',
                                  ],
                'notes'        => [
                                   'nullable',
                                   'string',
                                   'max:5000',
                                  ],
               ];
    }

    /**
     * @throws ValidationException
     */
    public function getLastContacted(): ?Carbon
    {
        if ($this->has('last_contact')) {
            try {
                return Carbon::createFromFormat('Y-m-d H:i', $this->last_contact);
            } catch (Exception) {
                throw ValidationException::withMessages(['last_contact' => 'invalid date format provided.']);
            }
        }
        return null;
    }
}
