<?php

namespace App\Http\Requests\Base;

use App\Enums\Core\RoleEnum;
use App\Exceptions\ErroredException;
use App\Models\Auth\Team;
use App\Models\Auth\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
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
                'share_role'  => [
                                  'required',
                                  Rule::in(RoleEnum::values()),
                                 ],
               ];
    }

    /**
     * @throws ValidationException
     */
    public function getParty(): User|Team
    {
        $party = $this->validated('share_party');
        if (Str::startsWith($party, 't#')) {
            $arr = explode('#', $party);
            array_shift($arr);
            $team = Team::query()->where('TeamID', implode('', $arr))->first();
            if (($team instanceof Team) && $team->users()->count() > 0) {
                return $team;
            }
            throw ValidationException::withMessages(['share_party' => 'invalid team or has no users']);
        }

        $user = User::query()->where('UserID', Str::upper($party))->first();
        if ($user instanceof User) {
            return $user;
        }
        throw ValidationException::withMessages(['share_party' => 'invalid user selected.']);
    }

    /**
     * @throws ValidationException
     */
    public function getRole(): RoleEnum
    {
        try {
            $role = RoleEnum::fromValue($this->validated('share_role'));
        } catch (ErroredException $e) {
            throw ValidationException::withMessages(['share_role' => 'invalid role provided']);
        }
        return $role;
    }
}
