<?php

namespace App\Http\Requests\Marketing;

use App\Exceptions\ErroredException;
use App\Models\Auth\User;
use App\Models\Core\Country;
use App\Models\Core\Locality;
use App\Models\ThirdParies\Competitor;
use App\Rules\isDomain;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Rules\Phone;
use Throwable;

class CompetitorRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'Name' => ['required', 'string', 'max:250',],
            'Country' => ['required', Rule::exists('t_Countries', 'CountryCode')],
            'Location' => ['required',],
            'Website' => ['nullable', 'url', 'max:250', new isDomain(),],
            'Phone' => ['nullable', (new Phone)->countryField('Country'),],
            'Email' => ['nullable', 'email', 'max:250',],
            "CoreBusiness" => ['nullable', 'string', 'max:250',],
            "Clients" => ['nullable', 'integer', 'min:1',],
            "MarketShare" => ['nullable', 'string', 'max:250',],
            /* "FinancialCapabilities" => ['nullable', 'string', 'max:7000'],
             "StrengthWeaknesses" => ['nullable', 'string', 'max:7000'],
             "CustomerPerception" => ['nullable', 'string', 'max:7000'],*/
            'Notes' => ['nullable', 'string', 'max:5000',],
            'image' => ['nullable', Rule::imageFile()->max('10mb'),],
        ];
    }

    public function messages(): array
    {
        return ['Phone.*' => 'invalid phone number provided.'];
    }

    /**
     * @throws ErroredException
     */
    public function save(User $actor, Competitor $competitor = null): Competitor
    {
        $image = $this->getImage();
        $new = (is_null($competitor)) ? ['CreatedBy' => $actor->Id] : [];
        $competitor = (is_null($competitor)) ? new Competitor() : $competitor;

        $location = $this->getLocation();

        try {
            return DB::transaction(function () use ($new, $competitor, $image, $actor, $location) {
            $competitor->fill(array_merge([
                "CompetitorName" => $this->validated('Name'),
                "LocationID" => $location->ID,
                'CountryId' => $location->CountryId,
                "CoreBusiness" => $this->validated('CoreBusiness'),
                "Clients" => $this->validated('Clients'),
                "MarketShare" => $this->validated('MarketShare'),
                "Email" => $this->validated('Email'),
                "Phone" => $this->getPhoneNumber(),
                "Website" => $this->validated('Website'),
                'Notes' => $this->validated('Notes'),
                'ModifiedBy' => $actor->Id,
            ], $new))->save();

            if (empty($new)) {
                activity()->causedBy($actor)->performedOn($competitor)->event('create')->log('Added a new competitor ' . $competitor->CompetitorName . '.');
            } else {
                activity()->causedBy($actor)->performedOn($competitor)->event('update')->log('Updated competitor (' . $competitor->CompetitorID . ')details.');
            }


            if ($image instanceof UploadedFile) {
                $competitor->setImage($image, $actor, 'Logo');
            }

            return $competitor;
        });
        } catch (Throwable $e) {
            Log::error('Error for competitor request: ' . $e->getMessage());
        }
        throw new ErroredException('an expected error occurred.');
    }

    public function getPhoneNumber(): string
    {
        return (new PhoneNumber($this->validated('Phone'), $this->validated('Country')))->formatE164();
    }

    public function getLocation(): Locality
    {
        $country = Country::query()->where('CountryCode', $this->validated('Country'))->first();
        if ($country instanceof Country) {
            $location = $country->localities()->where('ID', $this->validated('Location'))->first();
            if ($location instanceof Locality) {
                return $location;
            }
        }

        throw ValidationException::withMessages(['Location' => 'Location is not a valid location.']);
    }

    public function getImage(): ?UploadedFile
    {
        return $this->file('image');
    }
}
