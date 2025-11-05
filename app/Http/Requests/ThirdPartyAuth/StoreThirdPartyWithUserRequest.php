<?php

namespace App\Http\Requests\ThirdPartyAuth;

use App\Models\Core\CodeDetail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\BusinessTypeEnum;
use App\Enums\Employee\GenderEnum;
use App\Models\ThirdParty\ThirdPartyType;

class StoreThirdPartyWithUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

public function rules(): array
{
    $partyTypeValue = $this->input('PartyType');
    $isIndividual = false;

    // Detect individual based on Value or Description
    if ($partyTypeValue) {
        try {
            $type = CodeDetail::where('CodeID', 'PartyType')
                ->where(function ($q) use ($partyTypeValue) {
                    $q->where('Value', $partyTypeValue)
                      ->orWhere('Description', 'like', "%$partyTypeValue%");
                })
                ->first();

            if ($type) {
                $val = strtolower((string) $type->Value);
                $desc = strtolower((string) $type->Description);
                if (str_contains($val, 'in') || str_contains($desc, 'ind')) {
                    $isIndividual = true;
                }
            }
        } catch (\Throwable $e) {
            // ignore lookup failures and fall back to defaults
        }
    }

    // Base rules
    $rules = [
        'ThirdPartyName'  => ['required', 'string', 'max:255'],
        'TradingName'     => ['nullable', 'string', 'max:255'],
        'BusinessType'    => ['nullable', 'string', 'max:255'],
        'Country'         => ['required', 'string', 'max:255'],
        'PhysicalAddress' => ['required', 'string', 'max:255'],
        'Email'           => ['required', 'email', 'max:255', 'unique:t_ThirdParties,Email'],
        'Phone'           => ['required', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
        'Website'         => ['nullable', 'url', 'max:255'],
        'ThirdPartyType'  => ['required', 'array', 'min:1'],
        'ThirdPartyType.*' => ['integer', 'exists:t_ThirdPartyTypes,TypeId'],
        'PartyType'       => ['required', 'string', 'max:10'], // <-- add this
        'FirstName'       => ['required', 'string', 'max:50'],
        'LastName'        => ['required', 'string', 'max:50'],
        'UserEmail'       => ['required', 'email', 'max:254', 'unique:t_ThirdPartyUsers,Email'],
        'UserPhone'       => ['required', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
        'Gender'          => ['nullable', 'string', 'max:10'],
    ];

    if ($isIndividual) {
        $rules['IDNumber'] = ['nullable', 'string', 'max:100'];
        $rules['PassportNo'] = ['nullable', 'string', 'max:100'];
        $rules['RegistrationNumber'] = ['nullable', 'string', 'max:255'];
        $rules['TaxPIN'] = ['nullable', 'string', 'max:255'];
        $rules['VATNumber'] = ['nullable', 'string', 'max:255'];
    } else {
        $rules['RegistrationNumber'] = ['required', 'string', 'max:255', 'unique:t_ThirdParties,RegistrationNumber'];
        $rules['TaxPIN'] = ['nullable', 'string', 'max:255', 'unique:t_ThirdParties,TaxPIN'];
        $rules['VATNumber'] = ['nullable', 'string', 'max:255'];
    }

    return $rules;
}


    public function messages(): array
    {
        return [
            //'RegistrationNumber.unique' => 'This registration number already exists.',
            'TaxPIN.unique'             => 'This tax PIN already exists.',
            'Email.unique'              => 'This company email already exists.',
            'UserEmail.unique'          => 'This user email already exists.',
            'Phone.regex'               => 'Phone must be in international format, e.g., +254712345678.',
            'UserPhone.regex'           => 'User phone must be in international format, e.g., +254712345678.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalize company phone
        $raw = (string) ($this->input('Phone') ?? '');
        if ($raw !== '') {
            $digits = preg_replace('/\D+/', '', $raw);
            if (strlen($digits) >= 8 && strlen($digits) <= 15) {
                $this->merge(['Phone' => '+' . ltrim($digits, '+')]);
            }
        }

        // Normalize user phone
        $rawUser = (string) ($this->input('UserPhone') ?? '');
        if ($rawUser !== '') {
            $digitsUser = preg_replace('/\D+/', '', $rawUser);
            if (strlen($digitsUser) >= 8 && strlen($digitsUser) <= 15) {
                $this->merge(['UserPhone' => '+' . ltrim($digitsUser, '+')]);
            }
        }

        // ✅ Ensure ThirdPartyType is always an array
        $types = $this->input('ThirdPartyType');
        if (!is_array($types)) {
            $types = $types ? [$types] : [];
        }
        $this->merge(['ThirdPartyType' => $types]);
    }
}
