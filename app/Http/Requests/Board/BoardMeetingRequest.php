<?php

namespace App\Http\Requests\Board;

use App\Models\Auth\User;
use App\Models\CRM\MeetingRoom;
use App\Models\HRM\Committee;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BoardMeetingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'BoardMeetingCommittee' => [
                                            'required',
                                            Rule::exists('t_Committees', 'CommitteeID'),
                                           ],
                'BoardMeetingTitle'     => [
                                            'required',
                                            'string',
                                            'max:200',
                                           ],
                'BoardMeetingStart'     => [
                                            'required',
                                            'date_format:"Y-m-d H:i"',
                                            'before:BoardMeetingEnd',
                                           ],
                'BoardMeetingEnd'       => [
                                            'required',
                                            'date_format:"Y-m-d H:i"',
                                            'after:BoardMeetingStart',
                                           ],
                'BoardMeetingLocation'  => [
                                            'required',
                                            'string',
                                           ],
                'BoardMeetingAgenda'    => [
                                            'required',
                                            'string',
                                           ],
                'BoardMeetingUsers'     => [
                                            'required',
                                            'array',
                                            'min:1',
                                            'max:30',
                                           ],
               ];
    }

    public function getMeetingUsers(): array
    {
        return User::query()->whereIn('UserID', $this->validated('BoardMeetingUsers'))->select('t_Users.Id')->get('Id')->pluck('Id')->toArray();
    }

    public function getLocation(): string|MeetingRoom
    {
        $location = $this->validated('BoardMeetingLocation');
        if (Str::startsWith($location, 'ROOM')) {
            $room = MeetingRoom::where('RoomID', $location)->first();
            if ($room instanceof MeetingRoom) {
                return $room;
            }
        }
        return $location;
    }

    public function getCommittee(): Committee
    {
        $committee = Committee::query()->where('CommitteeID', $this->validated('BoardMeetingCommittee'))->first();
        if ($committee instanceof Committee) {
            if ($committee->members()->count() === 0) {
                throw ValidationException::withMessages(['BoardMeetingCommittee' => 'committee has no members']);
            }
            return $committee;
        }

        throw ValidationException::withMessages(['BoardMeetingCommittee' => 'invalid committee']);
    }

    /*
    * @throws ValidationException
    */
    public function getEnd(Carbon $start): Carbon
    {
        $end = Carbon::createFromFormat('Y-m-d H:i', $this->validated('BoardMeetingEnd'));
        if (!$end instanceof Carbon) {
            throw ValidationException::withMessages(['BoardMeetingEnd' => 'invalid date format']);
        }

        if ($end->lte($start)) {
            throw ValidationException::withMessages(['BoardMeetingEnd' => 'should be after start.']);
        }

        $diffInMinutes = $start->diffInMinutes($end, true);

        if ($diffInMinutes < 0) {
            throw ValidationException::withMessages(['BoardMeetingEnd' => 'duration should be less least 1 minute.']);
        }

        if ($diffInMinutes > 480) {//8 hours
            throw ValidationException::withMessages(['BoardMeetingEnd' => 'duration can only be a maximum of 8 hours.']);
        }

        return $end;
    }

    /**
     * @throws ValidationException
     */
    public function getStart(): Carbon
    {
        $start = Carbon::createFromFormat('Y-m-d H:i', $this->validated('BoardMeetingStart'));
        if ($start instanceof Carbon) {
            return $start;
        }
        throw ValidationException::withMessages(['meeting_start' => 'invalid date format']);
    }
}
