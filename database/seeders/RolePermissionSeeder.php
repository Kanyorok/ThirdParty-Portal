<?php

namespace Database\Seeders;

use App\Enums\Core\PermissionEnum;
use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Guard;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = SystemHelper::user();
        $date = now();
        $guard = Guard::getDefaultName(User::class);

        if (!Role::query()->where('name', 'Default')->exists()) {
            Role::create([
                'name' => 'Default',
                'guard_name' => $guard,
                'created_at' => $date,
                'updated_at' => $date,
                'CreatedBy' => $user->Id,
                'ModifiedBy' => $user->Id,
            ]);
        }


        $adminRole = Role::query()->createOrFirst(['name' => 'admin'], [
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);


        $permissions = collect([]);
        // Create permission
        foreach (PermissionEnum::cases() as $permission) {
            if (!Permission::query()->where('name', $permission->value)->exists()) {
                $permissions->add([
                    'name' => $permission->value,
                    'ModuleId' => $permission->module()->value,
                    'guard_name' => $guard,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }

        if ($permissions->count() > 0) {
            DB::table(config('permission.table_names.permissions'))->insert($permissions->toArray());

            // Assign permissions to roles
            $adminRole->permissions()->syncWithPivotValues(
                DB::table(config('permission.table_names.permissions'))->select('id')->get()->pluck('id')->toArray(),
                [
                    'CreatedBy' => $user->Id,
                    'ModifiedBy' => $user->Id,
                ],
                false
            );


            // Assign role to user
            $users = User::all();
            foreach ($users as $user) {
                if (($user instanceof User) && $user->roles()->count() === 0) {
                    $user->roles()->attach($adminRole->id, [
                        'BranchId' => $user->BranchId ?? 1, 
                        'CreatedBy' => 1,
                        'CreatedOn' => now(),
                        'ModifiedBy' => 1,
                        'ModifiedOn' => now(),
                    ]);
                }
            }
        }
    }
}
