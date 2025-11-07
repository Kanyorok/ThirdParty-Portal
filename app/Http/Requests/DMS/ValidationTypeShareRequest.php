<?php

namespace App\Http\Requests\DMS;

use App\Enums\Core\RoleEnum;
use App\Http\Requests\Core\ShareRequest;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ValidationTypeShareRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'approver' => ['required', 'string', 'max:200'],
            'role' => ['required', 'string', 'max:200']
        ];
    }

    public function getApprover(): Team|User
    {
        return (new ShareRequest())->getAssignee($this->str('approver')->trim()->toString(), 'approver');
    }

    public function getRole(): RoleEnum
    {
        $role = (new ShareRequest())->getRole($this->str('role')->trim()->toString());
        if (in_array($role->value, [RoleEnum::Admin->value, RoleEnum::Write->value], true)) {
            return $role;
        }

        throw ValidationException::withMessages([
            'role' => 'invalid or role not allowed.'
        ]);
    }

}
