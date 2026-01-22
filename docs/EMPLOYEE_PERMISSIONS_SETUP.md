# Employee Permissions Setup

## Overview
This document describes the portable permission setup for the Human Capital (HR) module's Employee management feature.

## Permissions Defined

The following permissions are defined in `app/Enums/Core/PermissionEnum.php`:

```php
// Human Resource management
case EmployeesView = 'employee-read';
case EmployeesCreate = 'employee-create';
case EmployeesUpdate = 'employee-update';
case EmployeesDelete = 'employee-delete';
```

These permissions are:
- **Grouped together** in the enum's `subModulesForView()` method under 'Employees'
- **Mapped to the HRM module** in the `module()` method
- **Used by the EmployeePolicy** to control access to employee CRUD operations

## Authorization Flow

1. **Controller**: `EmployeeController` uses `$this->authorizeResource(Employee::class)` in the constructor
2. **Policy**: `EmployeePolicy` checks permissions using `$user->can(PermissionEnum::EmployeesView->value)` etc.
3. **Database**: Permissions are stored in `t_Permissions` table and assigned via Spatie Permission package

## Deployment Instructions

### On Fresh Server/Database

Run the seeders in this order:

```bash
# 1. Run main database seeder (includes all base data)
php artisan db:seed

# OR run specific seeders manually:
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=EmployeePermissionsSeeder
```

### On Existing Server (Update)

If you're updating an existing server:

```bash
# 1. Pull latest code
git pull origin feature/hr

# 2. Run migrations (if any)
php artisan migrate

# 3. Run the employee permissions seeder
php artisan db:seed --class=EmployeePermissionsSeeder

# 4. Clear caches
php artisan cache:clear
php artisan config:clear
```

## What the Seeder Does

The `EmployeePermissionsSeeder` (`database/seeders/EmployeePermissionsSeeder.php`):

1. ✅ Creates the 4 employee permissions if they don't exist
2. ✅ Assigns them to the 'admin' role automatically
3. ✅ Clears the permission cache
4. ✅ Uses `firstOrCreate` to avoid duplicates (idempotent)
5. ✅ Works across all environments (portable)

## Files Modified

### Created:
- `database/seeders/EmployeePermissionsSeeder.php` - Dedicated seeder for employee permissions

### Updated:
- `database/seeders/DatabaseSeeder.php` - Added `EmployeePermissionsSeeder` to call stack
- `app/Models/Auth/User.php` - Added `CurrentSessionId` to fillable array
- `database/migrations/2026_01_20_152751_add_current_session_id_to_t_users_table.php` - Ran successfully

### Existing (No changes needed):
- `app/Enums/Core/PermissionEnum.php` - Permissions already defined
- `app/Policies/EmployeePolicy.php` - Policy already using correct permissions
- `app/Http/Controllers/HR/EmployeeController.php` - Controller already authorizing

## Verification

To verify permissions are set up correctly:

```bash
php artisan tinker
```

Then run:

```php
// Check permissions exist
\Spatie\Permission\Models\Permission::whereIn('name', [
    'employee-read', 
    'employee-create', 
    'employee-update', 
    'employee-delete'
])->get(['name', 'ModuleId']);

// Check admin role has permissions
$admin = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
$admin->permissions()->whereIn('name', [
    'employee-read', 
    'employee-create', 
    'employee-update', 
    'employee-delete'
])->pluck('name');

// Check a user has permissions (replace 8 with actual user ID)
$user = \App\Models\Auth\User::find(8);
$user->getAllPermissions()->whereIn('name', [
    'employee-read', 
    'employee-create', 
    'employee-update', 
    'employee-delete'
])->pluck('name');
```

## Troubleshooting

### 403 Forbidden when accessing /hr/employees

**Cause**: User doesn't have the required permissions

**Solution**:
```bash
php artisan db:seed --class=EmployeePermissionsSeeder
php artisan cache:clear
```

Then logout and login again to refresh session permissions.

### Permissions not showing after seeding

**Cause**: Permission cache not cleared

**Solution**:
```bash
php artisan cache:clear
php artisan config:clear
# In tinker:
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
```

## Notes

- The permissions use the naming convention `employee-{action}` (e.g., `employee-read`)
- All admin users automatically get these permissions via the seeder
- Non-admin users need to be explicitly granted these permissions or assigned to a role that has them
- The seeder is idempotent - safe to run multiple times

## Future Enhancements

Consider creating additional seeders for other HR submodules:
- Attendance permissions
- Payroll permissions
- Leave management permissions
- Training permissions
- etc.

Each should follow the same pattern as `EmployeePermissionsSeeder`.
