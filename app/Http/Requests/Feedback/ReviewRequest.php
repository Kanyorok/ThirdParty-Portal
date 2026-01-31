<?php

namespace App\Http\Requests\Feedback;

use App\Helpers\StringHelper;
use App\Models\BR\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ReviewRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'ratting' => [
                               'required_without:review',
                               'integer',
                               'between:1,5',
                              ],
                'review' => [
                               'required_without:ratting',
                               'string',
                               'max:5000',
                              ],
                'name' => [
                               'nullable',
                               'string',
                               'max:200',
                              ],
                'clientID' => [
                               'nullable',
                               'string',
                              ],
               ];
    }

    public function messages(): array
    {
        return [
                'ratting.required_without' => 'rate or / and review is required.',
                'review.required_without' => 'rate or / and review is required.',
               ];
    }

    /**
     * @throws ValidationException
     */
    public function getName(): string
    {
        $name = $this->validated('name');
        if (is_string($name)) {
            return $name;
        }

        throw ValidationException::withMessages(['name' => 'name is required']);
    }

    public function getRate(): int
    {
        $rate = $this->validated('ratting');

        return (StringHelper::isInteger($rate)) ? $rate : 3;
    }

    public function getReview(): string
    {
        $review = $this->validated('review');

        return (is_string($review)) ? $review : false;
    }

    /**
     * @throws ValidationException
     */
    public function getClient(): string
    {
        $clientID = $this->validated('clientID');
        if (! is_string($clientID)) {
            throw ValidationException::withMessages(['clientID' => 'clientID is required']);
        }

        if (! Client::query()->where('ClientID', $clientID)->exists()) {
            throw ValidationException::withMessages(['clientID' => 'clientID may be invalid']);
        }

        return $clientID;
    }
}
