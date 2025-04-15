<?php

namespace App\Http\Requests\Board;

use App\Models\MeetingRoom;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateBoardMeetingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'BoardMeetingTitle'    => [
                                           'required',
                                           'string',
                                           'max:200',
                                          ],
                'BoardMeetingStart'    => [
                                           'required',
                                           'date_format:"Y-m-d H:i"',
                                           'before:end',
                                          ],
                'BoardMeetingEnd'      => [
                                           'required',
                                           'date_format:"Y-m-d H:i"',
                                           'after:start',
                                          ],
                'BoardMeetingLocation' => [
                                           'required',
                                           'string',
                                          ],
                'BoardMeetingAgenda'   => [
                                           'required',
                                           'string',
                                          ],
                'BoardMeetingUpdate'   => [
                                           'required',
                                           Rule::in(['yes', 'no']),
                                          ],
               ];
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

    public function sendNotification(): bool
    {
        return $this->validated('BoardMeetingUpdate') === 'yes';
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
