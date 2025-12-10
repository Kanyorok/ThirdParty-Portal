<?php

namespace App\Http\Requests\Lead;

use App\Enums\Core\PermissionEnum;
use App\Enums\Employee\GenderEnum;
use App\Enums\LeadTypeEnum;
use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Country;
use App\Models\Core\Locality;
use App\Models\CRM\Lead;
use App\Services\StaticListsService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Propaganistas\LaravelPhone\PhoneNumber;
use Propaganistas\LaravelPhone\Rules\Phone;
use Throwable;

class NewLeadRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'Name' => ['required', 'string', 'min:3', 'max:250',],
            'Type' => ['required', Rule::in(LeadTypeEnum::values()),],
            'Country' => ['required', Rule::exists('t_Countries', 'CountryCode')],
            'Location' => ['required',],
            'Website' => ['nullable', 'url:http,https', 'active_url'],
            'Gender' => [
                'required_if:Type,' . LeadTypeEnum::Individual->value,
                Rule::in(GenderEnum::values()),
            ],
            'Surname' => ['required_if:Type,' . LeadTypeEnum::Individual->value, 'string', 'min:3', 'max:150',],
            'Industry' => [
                'required',
                Rule::exists('t_CodeDetails', 'ID')->where(function (Builder $query) {
                    return $query->where('CodeID', StaticListsService::Industries);
                }),
            ],
            'CustomerType' => ['required',
                Rule::exists('t_CodeDetails', 'ID')->where(function (Builder $query) {
                    return $query->where('CodeID', StaticListsService::CustomerType);
                }),
            ],
            'Source' => ['required',
                Rule::exists('t_CodeDetails', 'ID')->where(function (Builder $query) {
                    return $query->where('CodeID', StaticListsService::MarketingModes);
                }),
            ],
            'Phone' => ['required', (new Phone)->countryField('Country'),],
            'Email' => [
                'nullable', Rule::email()->rfcCompliant(strict: false)->validateMxRecord()->preventSpoofing(), 'max:250',
            ],
            'JobTitle' => ['nullable', 'string', 'max:200',],
            'LastContact' => ['nullable', 'date_format:"Y-m-d H:i"', 'before:now',],
            'image' => ['nullable', Rule::imageFile()->max('10mb'),],
            'Notes' => ['nullable', 'string', 'max:5000',],
            'RelationshipManager' => ['nullable', 'string',],
        ];
    }

    public function messages(): array
    {
        return [
            'Phone.*' => 'invalid phone number provided.'
        ];
    }

    public function getCustomerType(): CodeDetail
    {
        $code = CodeDetail::where('CodeID', StaticListsService::CustomerType)
            ->where('ID', $this->validated('CustomerType'))->first();
        if ($code instanceof CodeDetail) {
            return $code;
        }
        throw ValidationException::withMessages(['CustomerType' => 'invalid customer type given']);
    }

    public function getSource(): CodeDetail
    {
        $code = CodeDetail::where('CodeID', StaticListsService::MarketingModes)
            ->where('ID', $this->validated('Source'))->first();
        if ($code instanceof CodeDetail) {
            return $code;
        }
        throw ValidationException::withMessages(['Source' => 'invalid source given']);
    }

    public function getIndustry(): CodeDetail
    {
        $code = CodeDetail::where('CodeID', StaticListsService::Industries)
            ->where('ID', $this->validated('Industry'))->first();
        if ($code instanceof CodeDetail) {
            return $code;
        }
        throw ValidationException::withMessages(['Industry' => 'invalid industry given']);
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

    /**
     * @throws ValidationException
     * @throws Throwable
     */
    public function save(User $actor, Lead $lead = null): Lead
    {
        $update = ($lead instanceof Lead);
        $data = $this->_data($actor, $update);
        $image = $this->getImage();
        $lead = (is_null($lead)) ? new Lead() : $lead;
        return DB::transaction(static function () use ($update, $data, $lead, $image, $actor) {
            $lead->fill($data)->save();

            if ($image instanceof UploadedFile) {
                $lead->setImage($image, $actor, 'ImageId');
            }

            /*if (!$update) {
                //activity
                //  ActivityService::leadAdded($lead, $actor);
            }*/

            return $lead;
        });
    }

    /**
     * @throws ValidationException
     */
    protected function _data(User $actor, bool $update): array
    {
        return array_merge([
            "Name" => $this->validated('Name'),
            "Email" => $this->validated('Email'),
            "Phone" => $this->getPhoneNumber(),
            "Website" => $this->validated('Website'),
            "Gender" => $this->getGender(),
            "LocationID" => $this->validated('Location'),
            "Industry" => $this->validated('Industry'),
            "Source" => $this->validated('Source'),
            "CustomerType" => $this->validated('CustomerType'),
            "JobTitle" => $this->validated('JobTitle'),
            "Notes" => $this->validated('Notes'),
            "OtherNames" => $this->validated('Surname'),
            'ModifiedBy' => $actor->Id,

        ], $update ? [] : [
            "Type" => $this->validated('Type'),
            'CreatedBy' => $actor->Id,
            "LastContacted" => $this->getLastContacted(),
            "RelationshipManagerID" => $this->getAssignee(),
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function getGender(): GenderEnum
    {
        if ($this->Type === LeadTypeEnum::Company->value) {
            return GenderEnum::Other;
        }

        try {
            return GenderEnum::from($this->validated('Gender'));
        } catch (Exception) {
        }
        throw ValidationException::withMessages(['Gender' => 'invalid gender provided.']);
    }

    /**
     * @throws ValidationException
     */
    public function getLastContacted(): ?Carbon
    {
        if ($this->has('LastContact')) {
            try {
                return Carbon::createFromFormat('Y-m-d H:i', $this->LastContact);
            } catch (Exception) {
                throw ValidationException::withMessages(['LastContact' => 'invalid date format provided.']);
            }
        }
        return null;
    }

    /**
     * @throws ValidationException
     */
    public function getAssignee(): User
    {
        $userID = $this->validated('RelationshipManager');
        if (!is_string($userID)) {
            return $this->user();
        }
        $user = User::query()->where('t_Users.UserID', Str::upper($userID))->where('t_Users.UserID', '!=', SystemHelper::ID)->first(['Id', 'UserID']);
        if (!$user instanceof User) {
            throw ValidationException::withMessages(['RelationshipManager' => 'invalid user selected.']);
        }

        if ($user->Id === $this->user()->Id) {
            return $user;
        }

        if (!$this->user()->can(PermissionEnum::LeadDelegate->value)) {
            throw ValidationException::withMessages(['RelationshipManager' => 'You cannot assign to another person']);
        }

        if (!$user->can('viewAny', Lead::class)) {
            throw ValidationException::withMessages([
                'Assignee' => $user->Name . ' does not have permission to manage a lead.',
            ]);
        }

        return $user;
    }

    public function getImage(): ?UploadedFile
    {
        return $this->file('image');
    }
}
