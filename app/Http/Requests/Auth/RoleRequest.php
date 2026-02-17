<?php

namespace App\Http\Requests\Auth;

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
        $check_role = Role::query()->where('role_type', 'system');
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
            // Normalize DB permission name and request keys to lowercase for comparison
            $dbName = strtolower($permission->name);
            $dbKey = str_replace([' ', '.'], '_', $dbName);

            // Check if any input key matches the normalized DB key
            $inputKeys = collect($this->all())->keys()->map(fn ($k) => str_replace([' ', '.'], '_', strtolower($k)));

            // Direct check (optimization)
            if ($this->has($permission->name) || $this->has(str_replace([' ', '.'], '_', $permission->name))) {
                $selectedIds[] = $permission->id;

                continue;
            }

            // Case-insensitive fallback
            foreach ($this->all() as $key => $value) {
                if ($value !== 'on') {
                    continue;
                }
                $normalizedKey = str_replace([' ', '.'], '_', strtolower($key));
                if ($normalizedKey === $dbKey) {
                    $selectedIds[] = $permission->id;

                    break;
                }
            }
        }

        if (empty($selectedIds)) {
            throw ValidationException::withMessages(['permissions' => 'select at least one permission']);
        }

        return $selectedIds;
    }
}
