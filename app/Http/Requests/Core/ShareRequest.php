<?php

namespace App\Http\Requests\Core;

use App\Enums\Core\RoleEnum;
use App\Exceptions\ErroredException;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShareRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'assignee' => ['required', 'array', 'min:1', 'max:20'],
            'role' => ['required', 'string', 'max:20'],
        ];
    }

    public function getAssignee(string $assignee, string $field = 'assignee'): User|Team
    {
        if (Str::startsWith($assignee, 't#')) {
            $arr = explode('#', $assignee);
            array_shift($arr);
            $team = Team::query()->where('TeamID', implode('', $arr))->first();
            if (($team instanceof Team) && $team->users()->count() > 0) {
                return $team;
            }

            throw ValidationException::withMessages([$field => 'invalid team or has no users']);
        }

        $user = User::query()->where('UserID', Str::upper($assignee))->first();
        if ($user instanceof User) {
            return $user;
        }

        throw ValidationException::withMessages([$field => 'invalid user selected.']);
    }

    public function getRole(string $role, string $field = 'role'): RoleEnum
    {
        try {
            $roleEnum = RoleEnum::fromValue($role);
        } catch (ErroredException $e) {
            throw ValidationException::withMessages([$field => 'invalid role provided']);
        }

        return $roleEnum;
    }
}
