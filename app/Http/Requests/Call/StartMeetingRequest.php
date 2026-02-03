<?php

namespace App\Http\Requests\Call;

use App\Enums\ScheduleStatusEnum;
use App\Models\CRM\Meeting;
use App\Models\CRM\MeetingRoom;
use App\Models\CRM\Schedule;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StartMeetingRequest extends FormRequest
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
            'meeting_initiated' => ['required', 'date_format:"H:i"'],
            'meeting_initiated_title' => ['required', 'min:5', 'max:200'],
            'meeting_initiated_location' => ['required', 'min:5', 'max:200'],
            'meeting_schedule' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'meeting_initiated_title.required' => 'Meeting title is required.',
            'meeting_initiated_location.required' => 'Meeting location is required.',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function getSchedule(): ?Schedule
    {
        if ($this->validated('meeting_schedule') === self::NoSchedule) {
            return null;
        }

        $schedule = Schedule::query()->where('ScheduledType', Meeting::getPrimaryKey())
            ->where('ScheduleStatusID', '!=', ScheduleStatusEnum::Success->value)
            ->where('t_Schedule.ScheduleID', $this->validated('meeting_schedule'))->first();
        if ($schedule instanceof Schedule) {
            return $schedule;
        }

        throw ValidationException::withMessages(['meeting_initiated' => 'invalid schedule provide']);
    }

    public function getLocation(): string|MeetingRoom
    {
        $location = $this->validated('meeting_initiated_location');
        if (Str::startsWith($location, 'ROOM')) {
            $room = MeetingRoom::where('RoomID', $location)->first();
            if ($room instanceof MeetingRoom) {
                return $room;
            }
        }

        return $location;
    }

    /**
     * @throws ValidationException
     */
    public function getStart(): Carbon
    {
        $current_start = Carbon::createFromFormat('H:i', $this->validated('meeting_initiated'));
        if (! $current_start instanceof Carbon) {
            throw ValidationException::withMessages(['meeting_initiated' => 'invalid date format']);
        }
        if ($current_start->greaterThan(now())) {
            $current_start = now()->subMinute();
        }

        return $current_start;
    }
}
