<?php

namespace App\Http\Requests\Marketing;

use App\Enums\CampaignTypeEnum;
use App\Enums\EmailStatusEnum;
use App\Exceptions\ErroredException;
use App\Models\BR\DebtProduct;
use App\Models\MarketingList;
use App\Services\Marketing\ListService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CampaignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'Label'         => [
                                    'required',
                                    'string',
                                    'max:200',
                                   ],
                'MarketingList' => [
                                    'required',
                                    'string',
                                   ],
                'Type'          => [
                                    'required',
                                    Rule::enum(CampaignTypeEnum::class),
                                   ],
                'Notes'         => [
                                    'nullable',
                                    'string',
                                    'max:5000',
                                   ],
               ];
    }

    /**
     * @throws ValidationException
     */
    public function savable(bool $update = false): array
    {
        return array_merge([
                            'Label'           => $this->validated('Label'),
                            'Type'            => $this->getType()->value,
                            'MarketingListId' => $this->getList()->MarketingListID,
                            'Notes'           => $this->validated('Notes'),
                            'ModifiedBy'      => $this->user()->Id,
                           ], $update ? [] : [
                                              'CreatedBy'  => $this->user()->Id,
                                              'Status'     => EmailStatusEnum::Draft,
                                              'CampaignID' => $this->createID(),
                                             ]);
    }


    /**
     * @throws ValidationException
     */
    public function getType(): CampaignTypeEnum
    {
        try {
            return CampaignTypeEnum::fromValue($this->validated('Type'));
        } catch (ErroredException) {
        }
        throw ValidationException::withMessages(['Type' => 'invalid type']);
    }

    /**
     * @throws ValidationException
     */
    public function getList(): MarketingList
    {
        $ml = MarketingList::query()->where('slug', $this->validated('MarketingList'))->first();
        if ($ml instanceof MarketingList) {
            if ($ml->Source === DebtProduct::getPrimaryKey()/* && !auth()->user()->can('debt', MarketingList::class)*/) {
                throw ValidationException::withMessages(['MarketingList' => 'You do not have permission to create a campaign for loan list.']);
            }
            //check count
            $contacts = (new ListService($ml))->contacts();
            if ($contacts < 3) {
                throw ValidationException::withMessages(['MarketingList' => 'List must have at least 3 contacts to create a campaign.']);
            }
            return $ml;
        }

        throw ValidationException::withMessages(['MarketingList' => 'invalid Marketing List']);
    }
}
