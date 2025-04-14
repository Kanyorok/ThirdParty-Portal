<?php

namespace App\Http\Requests\Marketing;

use App\Enums\LocalityTypeEnum;
use App\Models\Competitor;
use App\Models\User;
use App\Rules\isDomain;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
                'Name'         => [
                                   'required',
                                   'string',
                                   'max:250',
                                  ],
                'Location'     => [
                                   'required',
                                   Rule::exists('t_Localities', 'ID')->where(function (Builder $query) {
                                                    return $query->where('LocationType', LocalityTypeEnum::City->value);
                                   }),
                                  ],
                'Website'      => [
                                   'nullable',
                                   'url',
                                   'max:250',
                                   new isDomain(),
                                  ],
                'Phone'        => [
                                   'nullable',
                                   'string',
                                   'max:250',
                                  ],
                'Email'        => [
                                   'nullable',
                                   'email',
                                   'max:250',
                                  ],
                "CoreBusiness" => [
                                   'nullable',
                                   'string',
                                   'max:250',
                                  ],
                "Clients"      => [
                                   'nullable',
                                   'integer',
                                   'min:1',
                                  ],
                "MarketShare"  => [
                                   'nullable',
                                   'string',
                                   'max:250',
                                  ],
            /* "FinancialCapabilities" => ['nullable', 'string', 'max:7000'],
             "StrengthWeaknesses" => ['nullable', 'string', 'max:7000'],
             "CustomerPerception" => ['nullable', 'string', 'max:7000'],*/
                'Notes'        => [
                                   'nullable',
                                   'string',
                                   'max:5000',
                                  ],
                'image'        => [
                                   'nullable',
                                   Rule::imageFile()->max('10mb'),
                                  ],
               ];
    }

    public function getImage(): ?UploadedFile
    {
        return $this->file('image');
    }


    /**
     * @throws Throwable
     */
    public function save(User $actor, Competitor $competitor = null): Competitor
    {
        $image = $this->getImage();
        $new = (is_null($competitor)) ? ['CreatedBy' => $actor->Id] : [];
        $competitor = (is_null($competitor)) ? new Competitor() : $competitor;

        return DB::transaction(function () use ($new, $competitor, $image, $actor) {

            $competitor->fill(array_merge([
                                           "CompetitorName" => $this->validated('Name'),
                                           "LocationID"     => $this->validated('Location'),
                                           "CoreBusiness"   => $this->validated('CoreBusiness'),
                                           "Clients"        => $this->validated('Clients'),
                                           "MarketShare"    => $this->validated('MarketShare'),
                                           "Email"          => $this->validated('Email'),
                                           "Phone"          => $this->validated('Phone'),
                                           "Website"        => $this->validated('Website'),
                                           'Notes'          => $this->validated('Notes'),
                                           'ModifiedBy'     => $actor->Id,
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
    }
}
