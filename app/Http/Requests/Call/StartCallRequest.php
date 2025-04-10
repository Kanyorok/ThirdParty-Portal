<?php

namespace App\Http\Requests\Call;

use App\Enums\ScheduleStatusEnum;
use App\Models\Call;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StartCallRequest extends FormRequest
{
    public const NoSchedule = 'NONE';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'call_initiated' => ['required', 'date_format:"H:i"'],
            'schedule' => ['required']
        ];
    }

    /**
     * @throws ValidationException
     */
    public function getSchedule(): ?Schedule
    {
        if ($this->validated('schedule') === self::NoSchedule) {
            return null;
        }

        $schedule = Schedule::query()->where('ScheduledType', Call::getPrimaryKey())
            ->where('ScheduleStatusID', '!=', ScheduleStatusEnum::Success->value)
            ->where('t_Schedule.ScheduleID', $this->validated('schedule'))->first();
        if ($schedule instanceof Schedule) {
            return $schedule;
        }

        throw ValidationException::withMessages([
            'schedule' => 'schedule not found',
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function getStart(): Carbon
    {
        $current_start = Carbon::createFromFormat('H:i', $this->validated('call_initiated'));
        if (!$current_start instanceof Carbon) {
            throw ValidationException::withMessages([
                'call_initiated' => 'invalid date format',
            ]);
        }
        if ($current_start->greaterThan(now())) {
            $current_start = now()->subMinute();
        }

        return $current_start;
    }
}
