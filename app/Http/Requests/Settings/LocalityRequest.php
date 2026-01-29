<?php

namespace App\Http\Requests\Settings;

use App\Enums\LocalityTypeEnum;
use App\Models\Core\Locality;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class LocalityRequest extends FormRequest
{
    public const DefaultPlaceId = 0;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'place_name' => [
                                 'required',
                                 'string',
                                 'min:1',
                                 'max:255',
                                ],
                'place_in' => [
                                 'required',
                                 'integer',
                                ],
               ];
    }

    /**
     * @throws ValidationException
     */
    public function getType(): string
    {
        if (! in_array($this->_type, LocalityTypeEnum::values(), true)) {
            throw ValidationException::withMessages(['place_name' => 'invalid Locality']);
        }

        return $this->_type;
    }

    /**
     * @throws ValidationException
     */
    public function getPlaceName(int $ignore = 0): string
    {
        $query = Locality::query();
        if (is_int($this->getLocatedIn())) {
            $query->where('LocalityID', $this->getLocatedIn());
        }
        if ($ignore !== 0) {
            $query->where('ID', '!=', $ignore);
        }

        if ($query->where('Name', $this->validated('place_name'))->exists()) {
            throw ValidationException::withMessages(['place_name' => 'place already exists']);
        }

        return $this->validated('place_name');
    }

    /**
     * @throws ValidationException
     */
    public function getLocatedIn(): ?int
    {
        $place = (int) $this->place_in;
        if ($place === self::DefaultPlaceId) {
            return null;
        }

        if (! Locality::query()->where('ID', $place)->exists()) {
            throw ValidationException::withMessages(['place_in' => 'invalid place']);
        }

        return $place;
    }
}
