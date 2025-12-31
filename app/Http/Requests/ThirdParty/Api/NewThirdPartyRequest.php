<?php

namespace App\Http\Requests\ThirdParty\Api;

use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Country;
use App\Models\Core\Locality;
use App\Services\ThirdParties\ThirdPartyService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Rules\Phone;

class NewThirdPartyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
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
                Rule::email()->rfcCompliant(strict: false)->validateMxRecord()->preventSpoofing(),
                Rule::unique('t_ThirdParties', 'Email')->whereNull('DeletedOn'),
                'max:250',
            ],
            'Phone' => ['required', (new Phone)->countryField('Country')],
            'PhysicalAddress' => ['nullable', 'string', 'max:200'],
            'types' => ['required', 'array', 'min:1'],
            'logo' => ['nullable', Rule::imageFile()->max(9000)],

            'user_FirstName' => ['nullable', Rule::requiredIf($this->boolean('createUser')), 'string', 'max:200'],
            'user_LastName' => ['nullable', Rule::requiredIf($this->boolean('createUser')), 'string', 'max:200'],
            'user_Email' => [
                'nullable',
                Rule::requiredIf($this->boolean('createUser')),
                Rule::email()->rfcCompliant(strict: false)->validateMxRecord()->preventSpoofing(),
                Rule::unique('t_ThirdPartyUsers', 'Email')->whereNull('DeletedOn'),
                'max:200'
            ],
            'user_Phone' => ['nullable', Rule::requiredIf($this->boolean('createUser')), 'string', 'max:200'],
            'user_Gender' => ['nullable', Rule::requiredIf($this->boolean('createUser')), 'string', 'max:200'],
            'user_Password' => [
                'nullable',
                Rule::requiredIf($this->boolean('createUser')),
                'string',
                'min:8',
                'confirmed'
            ],
            'user_Password_confirmation' => ['nullable', Rule::requiredIf($this->boolean('createUser')), 'string'],

            'customer_DateOfBirth' => [Rule::requiredIf(in_array(ThirdPartyService::TypeCustomer, $this->array('types'), true)), 'date'],
            'customer_Gender' => [Rule::requiredIf(in_array(ThirdPartyService::TypeCustomer, $this->array('types'), true)), 'string', 'max:200'],
            'customer_MaritalStatus' => [Rule::requiredIf(in_array(ThirdPartyService::TypeCustomer, $this->array('types'), true)), 'string', 'max:200'],
            'customer_Occupation' => [Rule::requiredIf(in_array(ThirdPartyService::TypeCustomer, $this->array('types'), true)), 'string', 'max:200'],

            'tenant_Remarks' => [Rule::requiredIf(in_array(ThirdPartyService::TypeTenant, $this->array('types'), true)), 'string', 'max:200'],
        ];
    }

    public function getOccupation(): CodeDetail
    {
        $occupation = CodeDetail::query()->where('CodeID', 'Occupation')->where('Value', $this->validated('customer_Occupation'))->first();
        if ($occupation instanceof CodeDetail) {
            return $occupation;
        }
        throw ValidationException::withMessages(['customer_Occupation' => 'Occupation is not a valid Occupation.']);
    }

    public function getMaritalStatus(): CodeDetail
    {
        $maritalStatus = CodeDetail::query()->where('CodeID', 'MaritalStatus')->where('Value', $this->validated('customer_MaritalStatus'))->first();
        if ($maritalStatus instanceof CodeDetail) {
            return $maritalStatus;
        }
        throw ValidationException::withMessages(['customer_MaritalStatus' => 'Marital Status is not a valid Marital Status.']);
    }

    public function getGender(string $field): CodeDetail
    {
        $gender = CodeDetail::query()->where('CodeID', 'Gender')->where('Value', $this->validated($field))->first();

        return $gender ?? throw ValidationException::withMessages([
            $field => "The selected gender for $field is invalid."
        ]);
        // if ($gender instanceof CodeDetail) {
        //     return $gender;
        // }
        // throw ValidationException::withMessages(['Gender' => 'Gender is not a valid Gender.']);

    }

    public function getLogo(): UploadedFile|null
    {
        $image = $this->file('logo');
        if ($image instanceof UploadedFile) {
            return $image;
        }
        return null;
    }

    public function getBusinessType(): CodeDetail
    {
        $type = CodeDetail::query()->where('CodeID', 'BusinessType')->where('Value', $this->validated('BusinessType'))->first();
        if ($type instanceof CodeDetail) {
            return $type;
        }
        throw ValidationException::withMessages(['BusinessType' => 'Business Type is not a valid Business Type.']);
    }

    public function getCountry(): Country
    {
        $country = Country::query()->where('CountryCode', $this->validated('Country'))->first();
        if ($country instanceof Country) {
            return $country;
        }
        throw ValidationException::withMessages(['Country' => 'Country is not a valid country.']);
    }

    public function getPhoneNumber(Country $country, string $field): string
    {
        $phoneNumber = new PhoneNumber($this->str($field)->trim()->toString(), $country->CountryCode);
        if ($phoneNumber->isValid()) {
            return $phoneNumber->formatE164();
        }
        throw ValidationException::withMessages([$field => 'invalid phone number provided.']);
    }

    public function getLocation(Country $country): Locality
    {
        $location = $country->localities()->where('ID', $this->validated('Location'))->first();
        if ($location instanceof Locality) {
            return $location;
        }

        throw ValidationException::withMessages(['Location' => 'Location is not a valid location.']);
    }

    public function messages(): array
    {
        return [
            'Phone.*' => 'invalid phone number provided.',
            'user_Phone.*' => 'invalid phone number provided.'
        ];
    }
}
