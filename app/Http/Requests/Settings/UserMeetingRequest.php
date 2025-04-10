<?php

namespace App\Http\Requests\Settings;

use App\Helpers\SystemHelper;
use App\Models\MeetingRoom;
use App\Models\User;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserMeetingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'StaffMeetingTitle' => ['required', 'string', 'max:200'],
            'StaffMeetingLocation' => ['required', 'string'],
            'StaffMeetingStart' => ['required', 'date_format:"Y-m-d H:i"', 'before:StaffMeetingEnd'],
            'StaffMeetingEnd' => ['required', 'date_format:"Y-m-d H:i"', 'after:StaffMeetingStart'],
            'StaffMeetingUsers' => ['required', 'array', 'min:1', 'max:30'],
            'StaffMeetingAgenda' => ['required', 'string'],
        ];
    }

    /*
  * @throws ValidationException
  */
    public function getEnd(Carbon $start): Carbon
    {
        $end = Carbon::createFromFormat('Y-m-d H:i', $this->validated('StaffMeetingEnd'));
        if (!$end instanceof Carbon) {
            throw ValidationException::withMessages([
                'StaffMeetingEnd' => 'invalid date format',
            ]);
        }

        if ($end->lte($start)) {
            throw ValidationException::withMessages([
                'StaffMeetingEnd' => 'should be after start.',
            ]);
        }

        $diffInMinutes = $start->diffInMinutes($end, true);

        if ($diffInMinutes < 0) {
            throw ValidationException::withMessages([
                'StaffMeetingEnd' => 'duration should be less least 1 minute.',
            ]);
        }

        if ($diffInMinutes > 480) {//8 hours
            throw ValidationException::withMessages([
                'StaffMeetingEnd' => 'duration can only be a maximum of 8 hours.',
            ]);
        }

        return $end;
    }

    /**
     * @throws ValidationException
     */
    public function getStart(): Carbon
    {
        $start = Carbon::createFromFormat('Y-m-d H:i', $this->validated('StaffMeetingStart'));
        if ($start instanceof Carbon) {
            return $start;
        }
        throw ValidationException::withMessages([
            'meeting_start' => 'invalid date format',
        ]);
    }

    public function getMeetingUsers(): array
    {
        $users = $this->collect('StaffMeetingUsers');
        if ($users->contains(UserService::MODULE)) {
            return User::query()->where('UserID', '!=', SystemHelper::ID)->select('t_Users.Id')->get('Id')->pluck('Id')->toArray();
        }

        $staff = User::query()->whereIn('UserID', $this->validated('StaffMeetingUsers'))->select('t_Users.Id')->get('Id')->pluck('Id')->toArray();

        if (count($staff) < 2) {
            throw ValidationException::withMessages([
                'StaffMeetingUsers' => 'should have at least 2 users.'
            ]);
        }

        return $staff;
    }

    public function getLocation(): string|MeetingRoom
    {
        $location = $this->validated('StaffMeetingLocation');
        if (Str::startsWith($location, 'ROOM')) {
            $room = MeetingRoom::where('RoomID', $location)->first();
            if ($room instanceof MeetingRoom) {
                return $room;
            }
        }
        return $location;
    }
}
