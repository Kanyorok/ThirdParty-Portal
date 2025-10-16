<?php

namespace Database\Seeders;

use App\Enums\Core\PermissionEnum;
use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
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
        $actor = SystemHelper::user();
        $now   = now();
        $guard = Guard::getDefaultName(User::class);

        // Ensure baseline roles
        if (!Role::query()->where('name', 'Default')->exists()) {
            Role::create([
                'name'       => 'Default',
                'guard_name' => $guard,
                'created_at' => $now,
                'updated_at' => $now,
                'CreatedBy'  => $actor->Id ?? 1,
                'ModifiedBy' => $actor->Id ?? 1,
            ]);
        }

        // createOrFirst avoids races/duplicates on name+guard
        $adminRole = Role::query()->createOrFirst(
            ['name' => 'admin', 'guard_name' => $guard],
            ['CreatedBy' => $actor->Id ?? 1, 'ModifiedBy' => $actor->Id ?? 1]
        );

        // --- Build permission rows (only those missing) ---
        $table = config('permission.table_names.permissions');

        $existing = DB::table($table)
            ->where('guard_name', $guard)
            ->pluck('name')
            ->all();
        $existing = array_flip($existing); // for O(1) existence checks

        $rows = [];
        foreach (PermissionEnum::cases() as $perm) {
            $name = $perm->value;
            if (!isset($existing[$name])) {
                $rows[] = [
                    'name'       => $name,
                    'ModuleId'   => $perm->module()->value,
                    'guard_name' => $guard,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // --- Upsert permissions in chunks to avoid 2100-param limit ---
        // 5 columns per row here => safe chunk ~400 rows
        $chunkSize = 400;

        if (!empty($rows)) {
            DB::connection()->disableQueryLog();
            DB::transaction(function () use ($table, $rows, $chunkSize) {
                foreach (array_chunk($rows, $chunkSize) as $chunk) {
                    // unique by (name, guard_name); update ModuleId/updated_at if re-running
                    DB::table($table)->upsert(
                        $chunk,
                        ['name', 'guard_name'],
                        ['ModuleId', 'updated_at']
                    );
                }
            });
        }

        // Fetch ALL permission ids for this guard (existing + newly inserted)
        $permissionIds = DB::table($table)
            ->where('guard_name', $guard)
            ->pluck('id')
            ->all();

        // --- Attach permissions to admin role in chunks ---
        // Pivot likely: role_has_permissions (role_id, permission_id, + your audit cols)
        // Each row binds ~2-6 params; stay well under 2100
        $pivotValues = [
            'CreatedBy'  => $actor->Id ?? 1,
            'ModifiedBy' => $actor->Id ?? 1,
        ];

        foreach (array_chunk($permissionIds, 500) as $permChunk) {
            // syncWithoutDetaching keeps existing links and adds missing ones
            $attachPayload = [];
            foreach ($permChunk as $pid) {
                $attachPayload[$pid] = $pivotValues;
            }
            $adminRole->permissions()->syncWithoutDetaching($attachPayload);
        }

        // --- Attach admin role to any user with no roles (also chunked) ---
        User::query()
            ->whereDoesntHave('roles')
            ->orderBy('Id')
            ->chunkById(500, function ($users) use ($adminRole) {
                $now = now();
                $payload = [];
                foreach ($users as $u) {
                    $payload[$adminRole->id] = [
                        'BranchId'   => $u->BranchId ?? 1,
                        'CreatedBy'  => 1,
                        'CreatedOn'  => $now,
                        'ModifiedBy' => 1,
                        'ModifiedOn' => $now,
                    ];
                    // Attach per-user (keeps memory low and avoids giant param batches)
                    $u->roles()->syncWithoutDetaching($payload);
                }
            });
    }
}
