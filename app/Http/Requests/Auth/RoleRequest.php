<?php

namespace App\Http\Requests\Auth;

use App\Enums\Core\PermissionEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'RoleName' => ['required', 'string', 'max:200'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function getName(Role $role = null): string
    {
        $name = $this->validated('RoleName');
        $check_role = Role::query();
        if ($role instanceof Role) {
            $check_role->where('id', '!=', $role->id);
        }
        if ($check_role->where('name', $name)->exists()) {
            throw ValidationException::withMessages([
                'RoleName' => "Role name already exists.",
            ]);
        }

        return $name;
    }

    /**
     * @throws ValidationException
     */
    public function getPermissions(): array
    {
        $roles = collect();

        foreach (PermissionEnum::getAll() as $permission) {
            if ($this->get($permission->value) === 'on') {
                $roles->push($permission);
            }
        }
        if ($roles->isEmpty()) {
            throw ValidationException::withMessages([
                'permissions' => 'select at least one permission',
            ]);
        }
        $permissions = Permission::query()->whereIn('name', $roles->toArray())->select('id')->pluck('id')->toArray();
        if (empty($permissions)) {
            throw ValidationException::withMessages([
                'permissions' => 'select at least one permission',
            ]);
        }

        return $permissions;
    }
}
