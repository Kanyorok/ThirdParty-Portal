<?php

namespace App\Http\Requests\Schedule;

use App\Enums\Core\PermissionEnum;
use App\Helpers\SystemHelper;
use App\Models\MeetingRoom;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MeetingScheduleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'meeting_title'    => [
                                       'required',
                                       'string',
                                       'max:250',
                                      ],
                'meeting_location' => [
                                       'required',
                                       'string',
                                       'max:250',
                                      ],
                'meeting_start'    => 'required|date_format:"Y-m-d H:i"|before:meeting_end',
                'meeting_end'      => 'required|date_format:"Y-m-d H:i"|after:meeting_start',
                'meeting_users'    => [
                                       'required',
                                       'array',
                                       'min:1',
                                       'max:30',
                                      ],
                'meeting_notes'    => [
                                       'required',
                                       'min:1',
                                       'max:250',
                                       'string',
                                      ],
               ];
    }

    public function getNotes(): string
    {
        return $this->validated('meeting_notes');
    }

    public function getLocation(): string|MeetingRoom
    {
        $location = $this->validated('meeting_location');
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
    public function getEnd(Carbon $start): Carbon
    {
        $end = Carbon::createFromFormat('Y-m-d H:i', $this->validated('meeting_end'));
        if (!$end instanceof Carbon) {
            throw ValidationException::withMessages(['meeting_start' => 'invalid date format']);
        }

        if ($end->lte($start)) {
            throw ValidationException::withMessages(['meeting_end' => 'should be after start.']);
        }

        $diffInMinutes = $start->diffInMinutes($end, true);

        if ($diffInMinutes < 0) {
            throw ValidationException::withMessages(['meeting_end' => 'duration should be less least 1 minute.']);
        }

        if ($diffInMinutes > 480) {//8 hours
            throw ValidationException::withMessages(['meeting_end' => 'duration can only be a maximum of 8 hours.']);
        }

        return $end;
    }

    /**
     * @throws ValidationException
     */
    public function getAssignees(): Collection
    {
        $users = User::query()->whereIn('t_Users.UserID', $this->validated('meeting_users'))->where('t_Users.UserID', '!=', SystemHelper::ID)->get(['Id', 'UserID']);
        if (!$users->count() === 0) {
            throw ValidationException::withMessages(['meeting_users' => 'no users selected']);
        }

        if ($users->count() === 1 && $users->first()->Id === $this->user()->Id) {
            return $users;
        }


        if (!$this->user()->can(PermissionEnum::ScheduleWrite->value)) {
            throw ValidationException::withMessages(['meeting_users' => 'You dont have permission to add people to meetings.']);
        }
        return $users;
    }

    /**
     * @throws ValidationException
     */
    public function getStart(): Carbon
    {
        $start = Carbon::createFromFormat('Y-m-d H:i', $this->validated('meeting_start'));
        if ($start instanceof Carbon) {
            return $start;
        }
        throw ValidationException::withMessages(['meeting_start' => 'invalid date format']);
    }
}
