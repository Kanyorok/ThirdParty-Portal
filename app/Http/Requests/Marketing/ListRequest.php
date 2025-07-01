<?php

namespace App\Http\Requests\Marketing;

use App\Enums\Core\VisibilityEnum;
use App\Enums\MarketingListEnum;
use App\Exceptions\ErroredException;
use App\Models\BR\Client;
use App\Models\CRM\Lead;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ListRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'Label' => ['required', 'string', 'min:2', 'max:100',],
            'Type' => ['nullable', Rule::enum(MarketingListEnum::class),],
            'Party' => ['nullable', 'string', 'required_if:Type,' . MarketingListEnum::Dynamic->value,],
            'Visibility' => ['required', Rule::enum(VisibilityEnum::class),],
            'Notes' => ['nullable', 'string', 'max:5000',],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function getSource(MarketingListEnum $type): ?string
    {
        $Source = $this->string('Party', 'null')->toString();
        if (!in_array($Source, ['null', Client::getPrimaryKey(), Lead::getPrimaryKey()], true)) {
            throw ValidationException::withMessages(['Party' => 'select a valid source']);
        }

        if ($type->value === MarketingListEnum::Dynamic->value && ($Source === 'null')) {
            throw ValidationException::withMessages(['Party' => 'select a valid source']);
        }

        return ($Source === 'null') ? null : $Source;
    }

    /**
     * @throws ValidationException
     */
    public function getType(): MarketingListEnum
    {
        try {
            return MarketingListEnum::fromValue($this->validated('Type'));
        } catch (ErroredException $e) {
            throw ValidationException::withMessages([
                'Type' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    public function getVisibility(): VisibilityEnum
    {
        $Visibility = $this->enum('Visibility', VisibilityEnum::class);
        if ($Visibility instanceof VisibilityEnum) {
            return $Visibility;
        }
        throw ValidationException::withMessages(['Visibility' => 'invalid visibility type']);
    }
}
