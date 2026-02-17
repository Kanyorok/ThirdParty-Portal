<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Merge t_HRJobRoles into t_Roles (Spatie permission roles table).
 *
 * This migration:
 * 1. Adds HR-specific columns (Code, GradeID, DepartmentID, Description, IsActive, DeletedBy, DeletedOn) to t_Roles
 * 2. Copies all t_HRJobRoles records into t_Roles (preserving their data)
 * 3. Updates all foreign keys in related tables to point from t_HRJobRoles → t_Roles
 * 4. Drops t_HRJobRoles
 *
 * After this migration, t_Roles is the single source of truth for both
 * system permission roles AND HR job roles.
 */
return new class extends Migration {
    public function up(): void
    {
        // Step 1: Add HR-specific columns to t_Roles
        // Note: ->after() is not supported on SQL Server, so we omit it.
        Schema::table('t_Roles', function (Blueprint $table) {
            if (!Schema::hasColumn('t_Roles', 'Code')) {
                $table->string('Code', 50)->nullable();
            }
            if (!Schema::hasColumn('t_Roles', 'GradeID')) {
                $table->unsignedBigInteger('GradeID')->nullable();
            }
            if (!Schema::hasColumn('t_Roles', 'DepartmentID')) {
                $table->unsignedBigInteger('DepartmentID')->nullable();
            }
            if (!Schema::hasColumn('t_Roles', 'Description')) {
                $table->string('Description', 255)->nullable();
            }
            if (!Schema::hasColumn('t_Roles', 'IsActive')) {
                $table->boolean('IsActive')->default(1);
            }
            if (!Schema::hasColumn('t_Roles', 'DeletedBy')) {
                $table->unsignedBigInteger('DeletedBy')->nullable();
            }
            if (!Schema::hasColumn('t_Roles', 'DeletedOn')) {
                $table->dateTime('DeletedOn')->nullable();
            }
            if (!Schema::hasColumn('t_Roles', 'role_type')) {
                $table->string('role_type', 20)->default('system');
                // 'system' = auth/permission role, 'job' = HR job role
            }
        });

        // Step 2: Copy t_HRJobRoles records into t_Roles
        if (Schema::hasTable('t_HRJobRoles')) {
            $jobRoles = DB::table('t_HRJobRoles')->get();
            $guard = config('auth.defaults.guard', 'web');

            // Build a mapping of old t_HRJobRoles.Id → new t_Roles.id
            $idMapping = [];

            foreach ($jobRoles as $jobRole) {
                // Check if a role with the same name already exists in t_Roles
                $existingRole = DB::table('t_Roles')
                    ->where('name', $jobRole->Name)
                    ->where('guard_name', $guard)
                    ->first();

                if ($existingRole) {
                    // Update existing role with HR data
                    DB::table('t_Roles')
                        ->where('id', $existingRole->id)
                        ->update([
                            'Code' => $jobRole->Code,
                            'GradeID' => $jobRole->GradeID,
                            'DepartmentID' => $jobRole->DepartmentID,
                            'Description' => $jobRole->Description,
                            'IsActive' => $jobRole->IsActive,
                            'role_type' => 'job',
                            'DeletedBy' => $jobRole->DeletedBy,
                            'DeletedOn' => $jobRole->DeletedOn,
                        ]);
                    $idMapping[$jobRole->Id] = $existingRole->id;
                } else {
                    // Insert new role
                    $newId = DB::table('t_Roles')->insertGetId([
                        'name' => $jobRole->Name,
                        'guard_name' => $guard,
                        'Code' => $jobRole->Code,
                        'GradeID' => $jobRole->GradeID,
                        'DepartmentID' => $jobRole->DepartmentID,
                        'Description' => $jobRole->Description,
                        'IsActive' => $jobRole->IsActive,
                        'role_type' => 'job',
                        'CreatedBy' => $jobRole->CreatedBy,
                        'ModifiedBy' => $jobRole->ModifiedBy,
                        'DeletedBy' => $jobRole->DeletedBy,
                        'DeletedOn' => $jobRole->DeletedOn,
                        'created_at' => $jobRole->CreatedOn,
                        'updated_at' => $jobRole->ModifiedOn,
                    ]);
                    $idMapping[$jobRole->Id] = $newId;
                }
            }

            // Step 3: Update all foreign keys that pointed to t_HRJobRoles.Id → t_Roles.id

            // 3a. t_HREmployees.RoleID
            if (Schema::hasTable('t_HREmployees') && Schema::hasColumn('t_HREmployees', 'RoleID')) {
                // Drop old FK if exists
                try {
                    Schema::table('t_HREmployees', function (Blueprint $table) {
                        $table->dropForeign(['RoleID']);
                    });
                } catch (\Exception $e) {
                    // FK may not exist, continue
                }

                // Update IDs
                foreach ($idMapping as $oldId => $newId) {
                    DB::table('t_HREmployees')
                        ->where('RoleID', $oldId)
                        ->update(['RoleID' => $newId]);
                }

                // Add new FK to t_Roles
                try {
                    Schema::table('t_HREmployees', function (Blueprint $table) {
                        $table->foreign('RoleID')->references('id')->on('t_Roles')->nullOnDelete();
                    });
                } catch (\Exception $e) {
                    // Continue if FK can't be added (data integrity issue)
                }
            }

            // 3b. t_HRJobRequisitions.RoleID
            if (Schema::hasTable('t_HRJobRequisitions') && Schema::hasColumn('t_HRJobRequisitions', 'RoleID')) {
                try {
                    Schema::table('t_HRJobRequisitions', function (Blueprint $table) {
                        $table->dropForeign(['RoleID']);
                    });
                } catch (\Exception $e) {}

                foreach ($idMapping as $oldId => $newId) {
                    DB::table('t_HRJobRequisitions')
                        ->where('RoleID', $oldId)
                        ->update(['RoleID' => $newId]);
                }

                try {
                    Schema::table('t_HRJobRequisitions', function (Blueprint $table) {
                        $table->foreign('RoleID')->references('id')->on('t_Roles')->nullOnDelete();
                    });
                } catch (\Exception $e) {}
            }

            // 3c. t_HRJobOpenings.RoleID
            if (Schema::hasTable('t_HRJobOpenings') && Schema::hasColumn('t_HRJobOpenings', 'RoleID')) {
                try {
                    Schema::table('t_HRJobOpenings', function (Blueprint $table) {
                        $table->dropForeign(['RoleID']);
                    });
                } catch (\Exception $e) {}

                foreach ($idMapping as $oldId => $newId) {
                    DB::table('t_HRJobOpenings')
                        ->where('RoleID', $oldId)
                        ->update(['RoleID' => $newId]);
                }

                try {
                    Schema::table('t_HRJobOpenings', function (Blueprint $table) {
                        $table->foreign('RoleID')->references('id')->on('t_Roles')->nullOnDelete();
                    });
                } catch (\Exception $e) {}
            }

            // 3d. t_HRSharedDocumentRoles.RoleID
            if (Schema::hasTable('t_HRSharedDocumentRoles') && Schema::hasColumn('t_HRSharedDocumentRoles', 'RoleID')) {
                try {
                    Schema::table('t_HRSharedDocumentRoles', function (Blueprint $table) {
                        $table->dropForeign(['RoleID']);
                    });
                } catch (\Exception $e) {}

                foreach ($idMapping as $oldId => $newId) {
                    DB::table('t_HRSharedDocumentRoles')
                        ->where('RoleID', $oldId)
                        ->update(['RoleID' => $newId]);
                }

                try {
                    Schema::table('t_HRSharedDocumentRoles', function (Blueprint $table) {
                        $table->foreign('RoleID')->references('id')->on('t_Roles')->onDelete('cascade');
                    });
                } catch (\Exception $e) {}
            }

            // Step 4: Drop the old t_HRJobRoles table
            Schema::dropIfExists('t_HRJobRoles');
        }
    }

    public function down(): void
    {
        // This migration is not safely reversible since data has been merged.
        throw new \Exception('This migration cannot be reversed. t_HRJobRoles has been merged into t_Roles.');
    }
};
