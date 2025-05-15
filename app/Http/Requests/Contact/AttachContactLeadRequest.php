<?php

namespace App\Http\Requests\Contact;

use App\Models\CRM\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class AttachContactLeadRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'lead' => [
                           'required',
                           'string',
                           'max:200',
                          ],
               ];
    }

    public function getLead(): Lead
    {
        $lead = Lead::where('LeadID', $this->validated('lead'))->first();
        if ($lead instanceof Lead) {
            return $lead;
        }
        throw ValidationException::withMessages(['lead' => 'lead not found, or invalid.']);
    }
}
