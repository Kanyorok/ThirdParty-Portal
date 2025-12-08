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
            'RoleName' => [
                'required',
                'string',
                'max:200',
            ],
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
            throw ValidationException::withMessages(['RoleName' => "Role name already exists."]);
        }

        return $name;
    }

    /**
     * @throws ValidationException
     */
    public function getPermissions(): array
    {
        $allPermissions = Permission::all();
        $selectedIds = [];

        foreach ($allPermissions as $permission) {
            // PHP converts dots and spaces to underscores in request keys
            $key = str_replace([' ', '.'], '_', $permission->name);

            if ($this->input($key) === 'on' || $this->input($permission->name) === 'on') {
                $selectedIds[] = $permission->id;
            }
        }

        if (empty($selectedIds)) {
            throw ValidationException::withMessages(['permissions' => 'select at least one permission']);
        }

        return $selectedIds;
    }
}
