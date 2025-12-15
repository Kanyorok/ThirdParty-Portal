<?php

namespace App\Http\Requests\ThirdParty;

use App\Enums\ThirdParty\ThirdPartyTypeEnum;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Country;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Rules\Phone;

class CreateThirdPartyProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('third_party')->check();
    }

    public function rules(): array
    {
        $isCustomer = $this->hasType(ThirdPartyTypeEnum::Customer);
        $isTenant = $this->hasType(ThirdPartyTypeEnum::Tenant);
        $isBusinessEntity = $this->hasType(ThirdPartyTypeEnum::Supplier) || $isTenant;

        return [
            'types' => ['required', 'array', 'min:1'],
            'types.*' => ['required', Rule::enum(ThirdPartyTypeEnum::class)],

            'Name' => ['required', 'string', 'min:2', 'max:255'],
            'Email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                'unique:t_ThirdParties,Email',
            ],
            'Phone' => ['required', (new Phone)->countryField('CountryCode')],
            'CountryCode' => ['required', 'string', 'exists:t_Countries,CountryCode'],
            'PhysicalAddress' => ['required', 'string', 'max:500'],

            'TradingName' => [Rule::requiredIf($isBusinessEntity), 'nullable', 'string', 'max:255'],
            'BusinessType' => [Rule::requiredIf($isBusinessEntity), 'nullable', 'exists:t_CodeDetails,Value,CodeID,BusinessType'],
            'RegistrationNumber' => [
                Rule::requiredIf($isBusinessEntity),
                'nullable',
                'string',
                'max:100',
                'unique:t_ThirdParties,RegistrationNumber',
            ],
            'TaxPIN' => ['nullable', 'string', 'max:50'],
            'Website' => ['nullable', 'url:https', 'max:255'],

            'Gender' => [Rule::requiredIf($isCustomer), 'nullable', 'exists:t_CodeDetails,Value,CodeID,Gender'],
            'MaritalStatus' => [Rule::requiredIf($isCustomer), 'nullable', 'exists:t_CodeDetails,Value,CodeID,MaritalStatus'],
            'Occupation' => [Rule::requiredIf($isCustomer), 'nullable', 'exists:t_CodeDetails,Value,CodeID,Occupation'],

            'TenantRemarks' => [Rule::requiredIf($isTenant), 'nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'types.required' => 'Select at least one profile type (Customer, Supplier, or Tenant).',
            'types.*.enum' => 'Invalid profile type selected.',
            'Email.unique' => 'This email is already registered to another entity.',
            'RegistrationNumber.unique' => 'This registration number already exists.',
            'Phone' => 'Invalid phone number for the selected country.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'Email' => strtolower(trim($this->Email ?? '')),
            'Name' => trim($this->Name ?? ''),
        ]);
    }

    public function hasType(ThirdPartyTypeEnum $type): bool
    {
        $types = $this->input('types', []);
        return in_array($type->value, $types, true) || in_array($type, $types, true);
    }

    public function getTypes(): array
    {
        return array_map(
            fn($type) => $type instanceof ThirdPartyTypeEnum ? $type : ThirdPartyTypeEnum::from($type),
            $this->validated('types')
        );
    }

    public function requiresApproval(): bool
    {
        return $this->hasType(ThirdPartyTypeEnum::Supplier);
    }

    public function getCountry(): Country
    {
        return Country::where('CountryCode', $this->validated('CountryCode'))->firstOrFail();
    }

    public function getFormattedPhone(): string
    {
        $country = $this->getCountry();
        $phone = new PhoneNumber($this->validated('Phone'), $country->CountryCode);

        if (!$phone->isValid()) {
            throw ValidationException::withMessages(['Phone' => 'Invalid phone number.']);
        }

        return $phone->formatE164();
    }

    public function getCodeDetail(string $codeId, string $field): ?CodeDetail
    {
        $value = $this->validated($field);
        if (!$value) {
            return null;
        }

        $detail = CodeDetail::where('CodeID', $codeId)->where('Value', $value)->first();

        if (!$detail) {
            throw ValidationException::withMessages([$field => "Invalid {$field} selected."]);
        }

        return $detail;
    }

    public function getBusinessType(): ?CodeDetail
    {
        return $this->getCodeDetail('BusinessType', 'BusinessType');
    }

    public function getGender(): ?CodeDetail
    {
        return $this->getCodeDetail('Gender', 'Gender');
    }

    public function getMaritalStatus(): ?CodeDetail
    {
        return $this->getCodeDetail('MaritalStatus', 'MaritalStatus');
    }

    public function getOccupation(): ?CodeDetail
    {
        return $this->getCodeDetail('Occupation', 'Occupation');
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
