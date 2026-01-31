<?php

namespace App\Http\Requests\Marketing;

use App\Models\BR\DebtProduct;
use App\Models\CRM\MarketingList;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PartyListRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'MarketingList' => [
                                      'nullable',
                                      'array',
                                     ],
                'MarketingList.*' => ['required'],
               ];
    }

    public function getLists(): array
    {
        $data = collect([]);
        if (is_array($this->validated('MarketingList'))) {
            foreach ($this->validated('MarketingList') as $item) {
                $ml = MarketingList::query()->where('Source', '!=', DebtProduct::getPrimaryKey())->where('slug', $item)->first();
                if ($ml instanceof MarketingList) {
                    $data->push((int) $ml->MarketingListID);
                }
            }
        }

        return $data->toArray();
    }
}
