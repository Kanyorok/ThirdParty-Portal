<?php

namespace App\Http\Requests\Schedule;

use App\Enums\Core\PermissionEnum;
use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CallScheduleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'call_start' => 'required|date_format:"Y-m-d H:i"|before:call_end',
                'call_end'   => 'required|date_format:"Y-m-d H:i"|after:call_start',
                'call_notes' => [
                                 'required',
                                 'min:1',
                                 'max:250',
                                 'string',
                                ],
                'call_user'  => [
                                 'required',
                                 'string',
                                ],
               ];
    }

    /**
     * @throws ValidationException
     */
    public function getAssignee(): User
    {
        $user = User::query()->where('t_Users.UserID', Str::upper($this->validated('call_user')))->where('t_Users.UserID', '!=', SystemHelper::ID)->first(['Id', 'UserID']);
        if (!$user instanceof User) {
            throw ValidationException::withMessages(['call_user' => 'invalid user']);
        }

        if ($user->Id === $this->user()->Id) {
            return $user;
        }

        if (!$this->user()->can(PermissionEnum::ScheduleWrite->value)) {
            throw ValidationException::withMessages(['call_user' => 'You cannot assign to another person']);
        }
        return $user;
    }
    public function getNotes(): string
    {
        return $this->validated('call_notes');
    }

    /**
     * @throws ValidationException
     */
    public function getEnd(Carbon $start): Carbon
    {
        $end = Carbon::createFromFormat('Y-m-d H:i', $this->input('call_end'));
        if (!$end instanceof Carbon) {
            throw ValidationException::withMessages(['call_start' => 'invalid date format']);
        }

        if ($end->lte($start)) {
            throw ValidationException::withMessages(['call_end' => 'should be after start.']);
        }

        $diffInMinutes = $start->diffInMinutes($end, true);

        if ($diffInMinutes < 0) {
            throw ValidationException::withMessages(['call_end' => 'duration should be less least 1 minute.']);
        }

        if ($diffInMinutes > 180) {//8 hours
            throw ValidationException::withMessages(['call_end' => 'duration can only be a maximum of 3 hours.']);
        }

        return $end;
    }

    /**
     * @throws ValidationException
     */
    public function getStart(): Carbon
    {
        $start = Carbon::createFromFormat('Y-m-d H:i', $this->input('call_start'));
        if ($start instanceof Carbon) {
            return $start;
        }
        throw ValidationException::withMessages(['call_start' => 'invalid date format']);
    }
}
