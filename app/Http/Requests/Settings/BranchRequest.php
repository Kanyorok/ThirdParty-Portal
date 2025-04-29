<?php

namespace App\Http\Requests\Settings;

use App\Models\CrmBranch;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class BranchRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $other = ($this->isMethod('post')) ? ['BranchID' => ['required', 'string', 'max:10']] : [];


        return array_merge([
            'Manager' => 'nullable',
            'Operation' => 'nullable',
            'Name' => ['required', 'string', 'max:255'],
            'Address' => ['nullable', 'string', 'max:255'],
            'Address2' => ['nullable', 'string', 'max:255'],
            'Phone' => ['nullable', 'string', 'max:40'],
            'Email' => ['nullable', 'email', 'max:255'],
        ], $other);
    }

    public function getBranchID(): string
    {
        $branchID = preg_replace('/[^a-zA-Z0-9]/', '', $this->string('BranchID')->toString());

        if (!is_string($branchID) || strlen($branchID) < 2) {
            throw ValidationException::withMessages([
                'BranchID' => 'invalid branch id, ate least 2 characters, no special characters allowed'
            ]);
        }
        if (CrmBranch::query()->where('BranchID', $branchID)->exists()) {
            throw ValidationException::withMessages([
                'BranchID' => 'branch id already exists'
            ]);
        }
        return $branchID;
    }

    public function getManager(): ?User
    {
        if ($this->has('Manager') && $this->string('Manager')->isNotEmpty()) {
            $user = User::query()->where('UserID', trim($this->string('Manager')->toString()))->first('Id');
            if (!$user instanceof User) {
                throw ValidationException::withMessages(['Manager' => 'Branch manager selected is invalid']);
            }
            return $user;
        }

        return null;
    }

    public function getOperation(): ?User
    {
        if ($this->has('Operation') && $this->string('Operation')->isNotEmpty()) {
            $user = User::query()->where('UserID', trim($this->string('Operation')->toString()))->first('Id');
            if (!$user instanceof User) {
                throw ValidationException::withMessages(['Operation' => 'Operational manager selected is invalid']);
            }
            return $user;
        }
        return null;
    }
}
