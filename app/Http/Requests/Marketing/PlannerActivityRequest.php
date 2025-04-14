<?php

namespace App\Http\Requests\Marketing;

use App\Helpers\SystemHelper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PlannerActivityRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'activity_name'      => [
                                         'required',
                                         'string',
                                         'max:200',
                                        ],
                'activity_location'  => [
                                         'required',
                                         'string',
                                         'max:200',
                                        ],
                'activity_budget'    => [
                                         'required',
                                         'numeric',
                                         'min:0',
                                        ],
                'activity_start'     => [
                                         'required',
                                         'date_format:"Y-m-d"',
                                         'before_or_equal:activity_end',
                                        ],
                'activity_end'       => [
                                         'required',
                                         'date_format:"Y-m-d"',
                                         'after_or_equal:activity_start',
                                        ],
                'activity_materials' => [
                                         'nullable',
                                         'string',
                                         'max:5000',
                                        ],
                'activity_notes'     => [
                                         'nullable',
                                         'string',
                                         'max:5000',
                                        ],
                'activity_users'     => [
                                         'required',
                                         'array',
                                         'min:1',
                                         'max:200',
                                        ],
               ];
    }

    /**
     * @throws ValidationException
     */
    public function getUsers(): Collection
    {
        $users = User::query()->whereIn('t_Users.UserID', $this->validated('activity_users'))->where('t_Users.UserID', '!=', SystemHelper::ID)->get(['Id', 'UserID']);
        if ($users->isEmpty()) {
            throw ValidationException::withMessages(['activity_users' => 'kindly select user(s) involved in the activity']);
        }
        return $users;
    }

    /**
     * @throws ValidationException
     */
    public function getEnd(Carbon $start): Carbon
    {
        $end = Carbon::createFromFormat('Y-m-d', $this->validated('activity_end'));
        if (!$end instanceof Carbon) {
            throw ValidationException::withMessages(['activity_end' => 'invalid date format']);
        }

        $end->endOfDay()->subSeconds(5);

        if ($end->lte($start)) {
            throw ValidationException::withMessages(['activity_end' => 'should be after start.']);
        }

        return $end;
    }

    /**
     * @throws ValidationException
     */
    public function getStart(): Carbon
    {
        $start = Carbon::createFromFormat('Y-m-d', $this->validated('activity_start'));
        if ($start instanceof Carbon) {
            return $start->startOfDay();
        }
        throw ValidationException::withMessages(['activity_start' => 'invalid date format']);
    }
}
