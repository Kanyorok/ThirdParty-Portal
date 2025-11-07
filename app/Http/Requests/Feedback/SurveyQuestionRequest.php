<?php

namespace App\Http\Requests\Feedback;

use App\Enums\Feedback\SurveyQuestionTypeEnum;
use App\Exceptions\ErroredException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class SurveyQuestionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'QuestionType'   => ['nullable'],
                'SurveyQuestion' => [
                                     'required',
                                     'min:3',
                                     'max:500',
                                    ],
                'SurveyHelp'     => [
                                     'nullable',
                                     'max:5000',
                                    ],
               ];
    }

    /**
     * @throws ValidationException
     */
    public function getQuestionType(): SurveyQuestionTypeEnum
    {
        if ($this->has('QuestionType')) {
            try {
                return SurveyQuestionTypeEnum::fromValue($this->validated('QuestionType'));
            } catch (ErroredException) {
            }
        }

        throw ValidationException::withMessages(['QuestionType' => 'invalid question type']);
    }
}
