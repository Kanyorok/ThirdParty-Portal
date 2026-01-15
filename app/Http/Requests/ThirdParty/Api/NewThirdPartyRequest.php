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
                'email',
                Rule::unique('t_ThirdPartyUsers', 'Email')->whereNull('DeletedOn'),
                'max:250',
            ],
            'user_Phone' => [Rule::requiredIf($isUser), 'nullable', 'string'],
            'user_Gender' => [Rule::requiredIf($isUser || $isCustomer), 'nullable', 'string'],
            'user_Password' => [
                Rule::requiredIf($isUser),
                'nullable',
                'string',
                'min:8',
                'confirmed'
            ],
            'supplier_category_id' => [
                'nullable',
                Rule::requiredIf($isSupplier),
                Rule::exists('t_SupplierCategories', 'SupplierCategoryID')
            ],
            'user_DateOfBirth' => ['nullable', Rule::requiredIf($isCustomer), 'date'],
            'user_MaritalStatus' => ['nullable', Rule::requiredIf($isCustomer), 'string'],
            'user_Occupation' => ['nullable', Rule::requiredIf($isCustomer), 'string'],
            'user_Remarks' => [
                'nullable',
                Rule::requiredIf($isTenant), 
                'string',
                'max:500'
            ],
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

        if (!$location) {
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
            'Phone.*' => 'invalid phone number provided.',
            'user_Phone.*' => 'invalid phone number provided.'
        ];
    }
}