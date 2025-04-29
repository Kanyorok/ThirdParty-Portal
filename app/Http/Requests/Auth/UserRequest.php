<?php

namespace App\Http\Requests\Auth;

use App\Enums\GenderEnum;
use App\Exceptions\ErroredException;
use App\Models\CrmBranch;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->_getuserId();
        return [
            'UserID' => ['required', 'string', 'max:100', 'min:3',],
            'Name' => ['required', 'string', 'max:255',],
            'Gender' => ['required', Rule::in(GenderEnum::values()),],
            'Phone' => ['required', 'min:9', 'max:13', 'regex: /^[(2541)(2547)]+[0-9]{9}$/i', Rule::unique('t_Users')->ignore($id, 'Id'),/*// 'unique:App\Models\User,Phone'*/],
            'Email' => ['required', 'email:rfc,dns', 'max:200', Rule::unique('t_Users')->ignore($id, 'Id'), /*'unique:App\Models\User,Phone'*/],
            //'ClientID' => ['required_if_declined:SyncAccount', 'max:20', Rule::unique('t_Users')->ignore($id, 'Id'), 'exists:App\Models\BR\Client,ClientID',],
            'Notes' => ['nullable', 'string', 'max:5000',],
            //'SyncAccount' => ['nullable'],
            'Branch' => ['nullable', 'string',],
            'Role' => ['nullable', 'string',],
            'Signature' => ['nullable', 'string', 'max:500000',],
        ];
    }

    private function _getuserId(): string
    {
        if ($this->is('user')) {
            return $this->user()->Id;
        }
        $params = $this->route()?->parameters();
        if (is_array($params) && array_key_exists('user', $params)) {
            return ($params['user'] instanceof User) ? $params['user']->Id : '';
        }

        return '0';
    }

    /**
     * @throws ValidationException
     */
    public function getBranch(): CrmBranch
    {
        if (!is_string($this->validated('Branch'))) {
            throw ValidationException::withMessages(['Branch' => 'Branch is required.']);
        }

        $branch = CrmBranch::query()->where('BranchID', $this->validated('Branch'))->first();
        if ($branch instanceof CrmBranch) {
            return $branch;
        }
        throw ValidationException::withMessages(['Branch' => 'Branch is not found.']);
    }

    /**
     * @throws ValidationException
     */
    public function getRole(): Role
    {
        $role = Role::query()->where('t_Roles.id', $this->validated('Role'))->first();
        if ($role instanceof Role) {
            return $role;
        }
        throw ValidationException::withMessages(['Role' => 'invalid role defined']);
    }


    /**
     * @throws ValidationException
     */
    public function getUserEmail(User $user = null): string
    {
        $Email = $this->validated('Email');
        $query = User::query()->where('Email', $Email);
        if ($user instanceof User) {
            $query->where('Id', '!=', $user->Id);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages(['Email' => 'Email already taken']);
        }

        return $Email;
    }

    /**
     * @throws ValidationException
     */
    public function getUserPhone(User $user = null): string
    {
        $Phone = $this->validated('Phone');
        if (!is_string($Phone)) {
            return '';
        }
        $query = User::query()->where('Phone', $Phone);
        if ($user instanceof User) {
            $query->where('Id', '!=', $user->Id);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages(['Phone' => 'Phone already taken']);
        }

        return $Phone;
    }

    /**
     * @throws ValidationException
     */
    public function getUserID(User $user = null): string
    {
        $UserID = Str::upper($this->validated('UserID'));
        $query = User::query()->where('UserID', $UserID);

        if ($user instanceof User) {
            $query->where('Id', '!=', $user->Id);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages(['UserID' => 'User already exists']);
        }

        if ($user instanceof User) {
            return $UserID;
        }


        /*$brexists = BRUser::where('OperatorID', $UserID)->exists();
        if ($this->sync()) {
            if ($brexists) {
                return $UserID;
            }
            throw ValidationException::withMessages(['UserID' => 'Sync on and user does not exist in Core.']);
        }


        if ($brexists) {
            throw ValidationException::withMessages([
                'UserID' => 'User already exists, if its you turn on sync',
                'SyncAccount' => 'turn on to sync account',
            ]);
        }*/
        return $UserID;
    }

    public function sync(): bool
    {
        return ($this->validated('SyncAccount') === 'on');
    }

    /**
     * @throws ValidationException
     */
    public function getGender(): GenderEnum
    {
        try {
            $gender = GenderEnum::fromValue($this->validated('Gender'));
        } catch (ErroredException $e) {
            throw ValidationException::withMessages(['Gender' => 'invalid gender provided']);
        }
        return $gender;
    }
}
