<?php

namespace App\Http\Requests\ThirdParty\Api;

use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Country;
use App\Models\Core\Locality;
use App\Services\ThirdParties\ThirdPartyService;
use App\Traits\Model\CodeDetailsTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Rules\Phone;

class NewThirdPartyRequest extends FormRequest
{
    use CodeDetailsTrait;

    protected function prepareForValidation(): void
    {
        $countryCode = $this->input('Country');

        if (! $countryCode) {
            return;
        }

        $country = Country::where('CountryCode', $countryCode)->first();

        if (! $country) {
            return;
        }

        $this->formatPhoneField('Phone', $country);
        $this->formatPhoneField('user_Phone', $country);

        // Map customer_ prefixed fields to user_ prefixed fields when user_ versions are missing
        $customerToUserFields = [
            'customer_DateOfBirth' => 'user_DateOfBirth',
            'customer_MaritalStatus' => 'user_MaritalStatus',
            'customer_Occupation' => 'user_Occupation',
            'customer_Gender' => 'user_Gender',
            'tenant_Remarks' => 'user_Remarks',
        ];

        foreach ($customerToUserFields as $customerField => $userField) {
            if (! $this->filled($userField) && $this->filled($customerField)) {
                $this->merge([$userField => $this->input($customerField)]);
            }
        }
    }

    private function formatPhoneField(string $field, Country $country): void
    {
        $value = $this->input($field);

        if (! $value) {
            return;
        }

        try {
            $formatted = (string) (new PhoneNumber($value, $country->CountryCode))->formatE164();
            $this->merge([$field => $formatted]);
        } catch (\Throwable) {
        }
    }

    public function rules(): array
    {
        $isUser = $this->boolean('createUser');
        $types = $this->input('types', []);
        $isCustomer = in_array(ThirdPartyService::TypeCustomer, $types, true);
        $isTenant = in_array(ThirdPartyService::TypeTenant, $types, true);
        $isSupplier = in_array(ThirdPartyService::TypeSupplier, $types, true);

        return [
            'Name' => ['required', 'string', 'max:255'],
            'TradingName' => ['nullable', 'string', 'max:255'],
            'BusinessType' => ['required', 'string', 'max:255'],
            'RegistrationNumber' => ['required', 'string', 'max:200'],
            'Website' => ['nullable', 'string', 'url:https', 'max:200'],
            'Country' => ['required', Rule::exists('t_Countries', 'CountryCode'), 'max:200'],
            'Location' => ['required'],
            'TaxPIN' => ['nullable', 'string', 'max:200'],
            'VATNumber' => ['nullable', 'string', 'max:200'],
            'Email' => ['nullable', 'email', 'max:250'],
            'Phone' => [
                'required',
                (new Phone())->countryField('Country'),
                Rule::unique('t_ThirdParties', 'Phone')->whereNull('DeletedOn'),
            ],
            'PhysicalAddress' => ['nullable', 'string', 'max:200'],
            'types' => ['required', 'array', 'min:1'],
            'logo' => ['nullable', Rule::imageFile()->max(9000)],
            'createUser' => ['boolean'],
            'user_FirstName' => [Rule::requiredIf($isUser), 'nullable', 'string', 'max:200'],
            'user_LastName' => [Rule::requiredIf($isUser), 'nullable', 'string', 'max:200'],
            'user_Email' => [
                Rule::requiredIf($isUser),
                'nullable',
                'email',
                Rule::unique('t_ThirdPartyUsers', 'Email')->whereNull('DeletedOn'),
                'max:250',
            ],
            'user_Phone' => [Rule::requiredIf($isUser), 'nullable', 'string'],
            'user_Gender' => [Rule::requiredIf($isUser || $isCustomer), 'nullable', 'string'],
            'user_Password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
            'supplier_category_id' => [
                'nullable',
                Rule::exists('t_SupplierCategories', 'SupplierCategoryID'),
            ],
            'user_DateOfBirth' => ['nullable', Rule::requiredIf($isCustomer), 'date'],
            'user_MaritalStatus' => ['nullable', Rule::requiredIf($isCustomer), 'string'],
            'user_Occupation' => ['nullable', Rule::requiredIf($isCustomer), 'string'],
            'user_Remarks' => [
                'nullable',
                Rule::requiredIf($isTenant),
                'string',
                'max:500',
            ],
            'customer_Gender' => ['nullable'],
            'customer_DateOfBirth' => ['nullable'],
            'customer_MaritalStatus' => ['nullable'],
            'customer_Occupation' => ['nullable'],
            'tenant_Remarks' => ['nullable'],
        ];
    }

    public function getBusinessType(): CodeDetail
    {
        $businessType = CodeDetail::query()
            ->where('CodeID', 'BusinessType')
            ->where('Value', $this->validated('BusinessType'))
            ->first();

        if ($businessType instanceof CodeDetail) {
            return $businessType;
        }

        throw ValidationException::withMessages(['BusinessType' => 'The selected business type is invalid.']);
    }

    public function getMaritalStatus(string $field): CodeDetail
    {
        return $this->getCodeDetail('MaritalStatus', $field);
    }

    public function getGender(string $field): CodeDetail
    {
        return $this->getCodeDetail('Gender', $field);
    }

    public function getOccupation(string $field): CodeDetail
    {
        return $this->getCodeDetail('Occupation', $field);
    }

    public function getCountry(): Country
    {
        return Country::where('CountryCode', $this->validated('Country'))->firstOrFail();
    }

    public function getLocation(Country $country): Locality
    {
        $location = $country->localities()->find($this->validated('Location'));

        if (! $location) {
            throw ValidationException::withMessages(['Location' => 'Location is not a valid location for the selected country.']);
        }

        return $location;
    }

    public function getPhoneNumber(Country $country, string $field): string
    {
        try {
            return (string) (new PhoneNumber($this->input($field), $country->CountryCode))->formatE164();
        } catch (\Exception $e) {
            throw ValidationException::withMessages([$field => 'The provided phone number is invalid.']);
        }
    }

    public function getLogo(): ?UploadedFile
    {
        return $this->file('logo');
    }

    public function messages(): array
    {
        return [
            'Phone.unique' => 'This phone number is already registered. Try a different number buddy!',
            'Phone.phone' => 'invalid phone number provided.',
            'Phone.country' => 'invalid phone number provided.',
            'user_Phone.*' => 'invalid phone number provided.',
        ];
    }
}
