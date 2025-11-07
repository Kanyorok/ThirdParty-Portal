<?php

namespace App\Http\Requests\Feedback;

use App\Models\CRM\Survey;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class SurveyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'Label' => [
                            'required',
                            'string',
                            'max:200',
                           ],
                'Start' => [
                            'required',
                            'date_format:"Y-m-d"',
                            'before_or_equal:End',
                           ],
                'End'   => [
                            'required',
                            'date_format:"Y-m-d"',
                            'after_or_equal:Start',
                           ],
                'Notes' => [
                            'nullable',
                            'string',
                            'max:5000',
                           ],
               ];
    }

    /**
     * @throws ValidationException
     */
    public function getEnd(Carbon $start, string $SurveyID = null): Carbon
    {
        $end = Carbon::createFromFormat('Y-m-d', $this->validated('End'));
        if (!$end instanceof Carbon) {
            throw ValidationException::withMessages(['Start' => 'invalid date format']);
        }
        $end->endOfDay()->subMinutes(10);

        if ($end->lte($start)) {
            throw ValidationException::withMessages(['End' => 'should be after start.']);
        }

        //check if overlapping
        if (
            Survey::query()->where('StartOn', '<=', $end)->where('EndOn', '>=', $end)
            ->where('SurveyID', '!=', $SurveyID)->exists()
        ) {
            throw ValidationException::withMessages(['End' => 'There is an overlapping survey.']);
        }

        return $end;
    }

    /**
     * @throws ValidationException
     */
    public function getStart(string $SurveyID = null): Carbon
    {
        $start = Carbon::createFromFormat('Y-m-d', $this->validated('Start'));
        if (!$start instanceof Carbon) {
            throw ValidationException::withMessages(['Start' => 'invalid date format']);
        }
        $start->startOfDay();

        //check if overlapping
        if (
            Survey::query()->where('StartOn', '<=', $start)->where('EndOn', '>=', $start)
            ->where('SurveyID', '!=', $SurveyID)->exists()
        ) {
            throw ValidationException::withMessages(['Start' => 'There is an overlapping survey.']);
        }

        return $start;
    }
}
