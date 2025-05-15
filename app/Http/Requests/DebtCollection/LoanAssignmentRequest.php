<?php

namespace App\Http\Requests\DebtCollection;

use App\Enums\Core\PermissionEnum;
use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoanAssignmentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'Assignee' => ['required'],
               ];
    }

    public function getAssignee(): User
    {
        $user = User::query()->where('t_Users.UserID', Str::upper($this->validated('Assignee')))
            ->where('t_Users.UserID', '!=', SystemHelper::ID)->first();
        if (!$user instanceof User) {
            throw ValidationException::withMessages(['Assignee' => 'invalid user selected']);
        }

        if ($user->Id === $this->user()->Id) {
            return $user;
        }

        if (!$user->can(PermissionEnum::DebtCollectionAssignment)) {
            throw ValidationException::withMessages([
                                                     'Assignee' => $user->Name . ' does not have permission to be assigned to a loan.',
                                                    ]);
        }

        return $user;
    }
}
