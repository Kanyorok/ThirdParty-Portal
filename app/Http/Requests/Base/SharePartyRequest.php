<?php

namespace App\Http\Requests\Base;

use App\Enums\Core\RoleEnum;
use App\Http\Requests\Core\ShareRequest;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SharePartyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'share_party' => ['required'],
            'share_role' => ['required', Rule::in(RoleEnum::values()),],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function getParty(): User|Team
    {
        return (new ShareRequest())->getAssignee($this->str('share_party'), 'share_party');
    }

    /**
     * @throws ValidationException
     */
    public function getRole(): RoleEnum
    {
        return (new ShareRequest())->getRole($this->str('share_role'), 'share_role');
    }
}
