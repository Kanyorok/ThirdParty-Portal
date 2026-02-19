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
        $actor = SystemHelper::user();
        $now = now();
        $guard = Guard::getDefaultName(User::class);

        // Ensure baseline roles
        if (! Role::query()->where('name', 'Default')->exists()) {
            Role::create([
                'name' => 'Default',
                'guard_name' => $guard,
                'created_at' => $now,
                'updated_at' => $now,
                'CreatedBy' => $actor->Id ?? 1,
                'ModifiedBy' => $actor->Id ?? 1,
            ]);
        }

        // createOrFirst avoids races/duplicates on name+guard
        $adminRole = Role::query()->createOrFirst(
            ['name' => 'admin', 'guard_name' => $guard],
            ['CreatedBy' => $actor->Id ?? 1, 'ModifiedBy' => $actor->Id ?? 1]
        );

        $table = config('permission.table_names.permissions');

        // 1. Get all valid permission names from Enum
        $validPermissions = [];
        foreach (PermissionEnum::cases() as $perm) {
            $validPermissions[] = $perm->value;
        }

        // 2. Collect permission IDs that are referenced by workflow stages (FK protected)
        $workflowPermissionIds = DB::table('t_WorkFlowStages')
            ->whereNotNull('PermissionId')
            ->distinct()
            ->pluck('PermissionId')
            ->all();

        // Also collect permission IDs referenced by workflow limits
        $workflowLimitPermissionIds = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('t_WorkFlowLimits') && \Illuminate\Support\Facades\Schema::hasColumn('t_WorkFlowLimits', 'PermissionId')) {
            $workflowLimitPermissionIds = DB::table('t_WorkFlowLimits')
                ->whereNotNull('PermissionId')
                ->distinct()
                ->pluck('PermissionId')
                ->all();
        }

        $protectedPermissionIds = array_unique(array_merge($workflowPermissionIds, $workflowLimitPermissionIds));

        // Delete permissions NOT in Enum AND NOT referenced by workflows
        $deleteQuery = DB::table($table)
            ->where('guard_name', $guard)
            ->whereNotIn('name', $validPermissions);

        if (! empty($protectedPermissionIds)) {
            $deleteQuery->whereNotIn('id', $protectedPermissionIds);
        }

        $deleted = $deleteQuery->delete();
        if ($deleted > 0) {
            echo "  Removed {$deleted} obsolete permissions." . PHP_EOL;
        }

            
        // 2. SKIP workflow permissions - they are special and belong to implemented workflows
        //    Do NOT delete, update, or touch workflow-related permissions as they impact approvals
        //    Get list of permissions that are referenced in workflow stages
        $workflowPermissionIds = DB::table('t_WorkFlowStages')
            ->whereNotNull('PermissionID')
            ->distinct()
            ->pluck('PermissionID')
            ->all();

        $workflowPermissionNames = DB::table($table)
            ->whereIn('id', $workflowPermissionIds)
            ->pluck('name')
            ->all();

        echo "Skipping " . count($workflowPermissionNames) . " workflow-related permissions..." . PHP_EOL;

        // 3. Prepare rows for Upsert
        $rows = [];

        foreach (PermissionEnum::cases() as $perm) {
            $rows[] = [
                'name' => $perm->value,
                'ModuleId' => $perm->module()->value,
                'guard_name' => $guard,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // 5 columns per row here => safe chunk ~400 rows
        $chunkSize = 400;

        if (! empty($rows)) {
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

        echo "Assigning " . count($permissionIds) . " permissions to admin role (excluding workflow permissions)..." . PHP_EOL;

        // Pivot likely: role_has_permissions (role_id, permission_id, + your audit cols)
        // Each row binds ~2-6 params; stay well under 2100
        $pivotValues = [
            'CreatedBy' => $actor->Id ?? 1,
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

        User::query()
            ->whereDoesntHave('roles')
            ->orderBy('Id')
            ->chunkById(500, function ($users) use ($adminRole) {
                $now = now();
                $payload = [];
                foreach ($users as $u) {
                    // t_ModelRoles does not have CreatedBy/ModifiedBy; only use existing columns
                    $payload[$adminRole->id] = [
                        'BranchId' => $u->BranchId ?? 1,
                        'CreatedOn' => $now,
                        'ModifiedOn' => $now,
                    ];
                    // Attach per-user (keeps memory low and avoids giant param batches)
                    // Ensure morph type uses our alias key 'UserID'
                    $u->setRelation('roles', null); // prevent cached relations side-effects
                    $u->roles()->syncWithoutDetaching($payload);
                }
            });
    }
}
