<?php

namespace App\Http\Requests\Settings;

use App\Models\Core\CodeDetail;
use App\Services\StaticListsService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class CodeDetailsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'Description' => [
                                  'required',
                                  'string',
                                  'min:1',
                                  'max:255',
                                 ],
               ];
    }

    /**
     * @throws ValidationException
     */
    public function getDescription(string $staticList, int $ignore = 0): string
    {
        $query = CodeDetail::query()->where('CodeID', $staticList);

        if ($ignore !== 0) {
            $query->where('ID', '!=', $ignore);
        }

        if ($query->where('Description', $this->validated('Description'))->exists()) {
            throw ValidationException::withMessages(['Description' => 'item already exists']);
        }

        return $this->validated('Description');
    }

    /**
     * @throws ValidationException
     */
    public function getType(): string
    {
        if (!in_array($this->_type, StaticListsService::getLists()->toArray())) {
            throw ValidationException::withMessages(['Description' => 'invalid list type']);
        }

        return $this->_type;
    }
}
