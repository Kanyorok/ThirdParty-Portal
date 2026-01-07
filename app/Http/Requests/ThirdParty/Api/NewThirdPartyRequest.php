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

    public function rules(): array
    {
        $isUser = $this->boolean('createUser');
        $types = $this->array('types');

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
            'Email' => [
                'nullable',
                Rule::email()->rfcCompliant()->validateMxRecord()->preventSpoofing(),
                'max:250',
            ],
            'Phone' => ['required', (new Phone)->countryField('Country')],
            'PhysicalAddress' => ['nullable', 'string', 'max:200'],
            'types' => ['required', 'array', 'min:1'],
            'logo' => ['nullable', Rule::imageFile()->max(9000)],

            'createUser' => ['boolean'],
            'user_FirstName' => [Rule::requiredIf($isUser), 'nullable', 'string', 'max:200'],
            'user_LastName' => [Rule::requiredIf($isUser), 'nullable', 'string', 'max:200'],
            'user_Email' => [
                Rule::requiredIf($isUser),
                'nullable',
                Rule::email()->rfcCompliant()->validateMxRecord()->preventSpoofing(),
                Rule::unique('t_ThirdPartyUsers', 'Email')->whereNull('DeletedOn'),
                'max:200'
            ],
            'user_Phone' => [Rule::requiredIf($isUser), 'nullable', 'string', 'max:200'],
            'user_Gender' => [Rule::requiredIf($isUser), 'nullable', 'string', 'max:200'],
            'user_Password' => [
                Rule::requiredIf($isUser),
                'nullable',
                'string',
                'min:8',
                'confirmed'
            ],
            'user_Password_confirmation' => [Rule::requiredIf($isUser), 'nullable', 'string'],

            'supplier_category_id' => [
                'nullable',
                Rule::requiredIf(in_array(ThirdPartyService::TypeSupplier, $types, true)),
                Rule::exists('t_SupplierCategories', 'SupplierCategoryID')
            ],

            'customer_DateOfBirth' => ['nullable', Rule::requiredIf(in_array(ThirdPartyService::TypeCustomer, $types, true)), 'date'],
            'customer_Gender' => ['nullable', Rule::requiredIf(in_array(ThirdPartyService::TypeCustomer, $types, true)), 'string', 'max:200'],
            'customer_MaritalStatus' => ['nullable', Rule::requiredIf(in_array(ThirdPartyService::TypeCustomer, $types, true)), 'string', 'max:200'],
            'customer_Occupation' => ['nullable', Rule::requiredIf(in_array(ThirdPartyService::TypeCustomer, $types, true)), 'string', 'max:200'],

            'tenant_Remarks' => ['nullable', Rule::requiredIf(in_array(ThirdPartyService::TypeTenant, $types, true)), 'string', 'max:200'],
        ];
    }

    public function getBusinessType(): CodeDetail
    {
        return $this->getCodeDetail('BusinessType', 'BusinessType');
    }

    public function getGender(string $field): CodeDetail
    {
        return $this->getCodeDetail('Gender', $field);
    }

    public function getMaritalStatus(): CodeDetail
    {
        return $this->getCodeDetail('MaritalStatus', 'customer_MaritalStatus');
    }

    public function getOccupation(): CodeDetail
    {
        return $this->getCodeDetail('Occupation', 'customer_Occupation');
    }

    public function getSupplierData(): array
    {
        return [
            'category_id' => $this->validated('supplier_category_id'),
        ];
    }

    public function getLogo(): ?UploadedFile
    {
        return $this->file('logo');
    }

    public function getCountry(): Country
    {
        return Country::where('CountryCode', $this->validated('Country'))->firstOrFail();
    }

    public function getLocation(Country $country): Locality
    {
        $location = $country->localities()->find($this->validated('Location'));

        if (!$location) {
            throw ValidationException::withMessages(['Location' => 'Location is not a valid location.']);
        }

        return $location;
    }

    public function getPhoneNumber(Country $country, string $field): string
    {
        $phoneNumber = new PhoneNumber($this->str($field)->trim()->toString(), $country->CountryCode);

        if ($phoneNumber->isValid()) {
            return $phoneNumber->formatE164();
        }

        throw ValidationException::withMessages([$field => 'invalid phone number provided.']);
    }

    public function messages(): array
    {
        return [
            'Phone.*' => 'invalid phone number provided.',
            'user_Phone.*' => 'invalid phone number provided.'
        ];
    }
}
