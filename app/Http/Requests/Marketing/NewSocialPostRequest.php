<?php

namespace App\Http\Requests\Marketing;

use App\Enums\Core\IntegrationsEnum;
use App\Exceptions\ErroredException;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class NewSocialPostRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'Destination' => ['required', 'array', 'min:1'],
            'Publish_On' => ['required', 'date_format:"Y-m-d H:i"'],
            'Content' => ['required', 'string', 'max:300', 'min:2'],
            'image' => ['required', Rule::imageFile()->max(5000)]
        ];
    }

    /**
     * @throws ValidationException
     */
    public function getDestinations(): Collection
    {
        $types = collect();
        foreach ($this->validated('Destination') as $type) {
            try {
                $dest = IntegrationsEnum::fromValue($type);
                if ($dest->isSocial()) {
                    $types->push($dest);
                    continue;
                }
            } catch (ErroredException) {
            }
            throw ValidationException::withMessages([
                'Destination' => 'some of the selected options are invalid.',
            ]);
        }

        if ($types->isEmpty()) {
            throw ValidationException::withMessages([
                'Destination' => 'some of the selected options are invalid.',
            ]);
        }
        return $types;
    }

    /**
     * @throws ValidationException
     */
    public function getDate(): Carbon
    {
        $start = Carbon::createFromFormat('Y-m-d H:i', $this->validated('Publish_On'));
        if ($start instanceof Carbon) {
            if ($start->lessThan(Carbon::now()->subMinutes(10))) {
                throw ValidationException::withMessages([
                    'Publish_On' => 'scheduled the future or now.',
                ]);
            }
            return $start;
        }
        throw ValidationException::withMessages([
            'Publish_On' => 'invalid date format',
        ]);
    }
}

