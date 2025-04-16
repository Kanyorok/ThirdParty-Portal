<?php

namespace App\Http\Requests\Schedule;

use App\Helpers\SystemHelper;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PartyTaskRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'task_date'  => 'required|date_format:"Y-m-d"',
                'task_notes' => [
                                 'required',
                                 'min:1',
                                 'max:1000',
                                 'string',
                                ],
                'task_user'  => [
                                 Rule::requiredIf($this->isMethod('POST')),
                                 'string',
                                ],
               ];
    }

    public function getAssignee(): User
    {
        $user = User::query()->where('t_Users.UserID', Str::upper($this->validated('task_user')))->where('t_Users.UserID', '!=', SystemHelper::ID)->first(['Id', 'UserID']);
        if (!$user instanceof User) {
            throw ValidationException::withMessages(['task_user' => 'invalid user']);
        }

        if ($user->Id === $this->user()->Id) {
            return $user;
        }

        if (!$this->user()->can('delegate', Task::class)) {
            throw ValidationException::withMessages(['task_user' => 'You cannot assign to another person']);
        }
        return $user;
    }

    public function getNotes(): string
    {
        return $this->validated('task_notes');
    }

    /**
     * @throws ValidationException
     */
    public function getDated(Carbon $current = null): Carbon
    {
        $date = Carbon::createFromFormat('Y-m-d', $this->input('task_date'));
        if (!$date instanceof Carbon) {
            throw ValidationException::withMessages(['task_date' => 'invalid date format']);
        }

        if (!$current instanceof Carbon) {//not an update
            $current = now();
        }

        if ($date->lte($current->subDay())) {
            throw ValidationException::withMessages(['task_date' => 'has to be due in the future.']);
        }

        return $date->endOfDay();
    }
}
