<?php

namespace Database\Seeders;

use App\Enums\Core\PermissionEnum;
use App\Helpers\SystemHelper;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EmployeePermissionsSeeder extends Seeder
{
    /**
     * Seed employee permissions and assign to admin role.
     */
    public function run(): void
    {
        $actor = SystemHelper::user();
        $now = now();
        $guard = 'web';

        // Define employee permissions from enum
        $employeePermissions = [
            PermissionEnum::EmployeesView->value,      // 'employee-read'
            PermissionEnum::EmployeesCreate->value,    // 'employee-create'
            PermissionEnum::EmployeesUpdate->value,    // 'employee-update'
            PermissionEnum::EmployeesDelete->value,    // 'employee-delete'
        ];

        // Create permissions if they don't exist
        foreach ($employeePermissions as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => $guard],
                [
                    'ModuleId' => PermissionEnum::EmployeesView->module()->value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // Get admin role
        $adminRole = Role::where('name', 'admin')->where('guard_name', $guard)->first();

        if ($adminRole) {
            // Assign permissions to admin role (syncWithoutDetaching won't remove existing permissions)
            $adminRole->givePermissionTo($employeePermissions);

            $this->command->info('✓ Employee permissions created and assigned to admin role');
        } else {
            $this->command->warn('⚠ Admin role not found. Please run RolePermissionSeeder first.');
        }

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('✓ Permission cache cleared');
    }
}

<?php

namespace Database\Seeders;

use App\Enums\Core\PermissionEnum;
use App\Helpers\SystemHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EmployeePermissionsSeeder extends Seeder
{
    /**
     * Seed employee permissions and assign to admin role.
     */
    public function run(): void
    {
        $actor = SystemHelper::user();
        $now = now();
        $guard = 'web';

        // Define employee permissions from enum
        $employeePermissions = [
            PermissionEnum::EmployeesView->value,      // 'employee-read'
            PermissionEnum::EmployeesCreate->value,    // 'employee-create'
            PermissionEnum::EmployeesUpdate->value,    // 'employee-update'
            PermissionEnum::EmployeesDelete->value,    // 'employee-delete'
        ];

        // Create permissions if they don't exist
        foreach ($employeePermissions as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => $guard],
                [
                    'ModuleId' => PermissionEnum::EmployeesView->module()->value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // Get admin role
        $adminRole = Role::where('name', 'admin')->where('guard_name', $guard)->first();

        if ($adminRole) {
            // Assign permissions to admin role (syncWithoutDetaching won't remove existing permissions)
            $adminRole->givePermissionTo($employeePermissions);
            
            $this->command->info('✓ Employee permissions created and assigned to admin role');
        } else {
            $this->command->warn('⚠ Admin role not found. Please run RolePermissionSeeder first.');
        }

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $this->command->info('✓ Permission cache cleared');
    }
}
