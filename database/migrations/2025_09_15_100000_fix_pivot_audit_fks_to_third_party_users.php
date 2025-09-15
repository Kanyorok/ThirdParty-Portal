<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = 't_ThirdPartyType_ThirdParties';

        if (!Schema::hasTable($table)) {
            return;
        }

        // Drop existing FKs (CreatedBy/ModifiedBy/DeletedBy) pointing to t_Users
        $driver = DB::getDriverName();
        if ($driver === 'sqlsrv') {
            $constraints = DB::select("SELECT f.name AS constraint_name FROM sys.foreign_keys f JOIN sys.tables t ON f.parent_object_id = t.object_id WHERE t.name = ?", [$table]);
            foreach ($constraints as $c) {
                $name = strtolower($c->constraint_name);
                if (str_contains($name, 'createdby') || str_contains($name, 'modifiedby') || str_contains($name, 'deletedby')) {
                    try { DB::statement('ALTER TABLE '.$table.' DROP CONSTRAINT '.$c->constraint_name); } catch (\Throwable $e) { /* ignore */ }
                }
            }
            // Also try default-style names in both cases
            foreach ([
                't_ThirdPartyType_ThirdParties_CreatedBy_foreign',
                't_ThirdPartyType_ThirdParties_ModifiedBy_foreign',
                't_ThirdPartyType_ThirdParties_DeletedBy_foreign',
                't_thirdpartytype_thirdparties_createdby_foreign',
                't_thirdpartytype_thirdparties_modifiedby_foreign',
                't_thirdpartytype_thirdparties_deletedby_foreign',
            ] as $fk) {
                try { DB::statement("IF OBJECT_ID(N'{$fk}', N'F') IS NOT NULL ALTER TABLE {$table} DROP CONSTRAINT {$fk}"); } catch (\Throwable $e) { /* ignore */ }
            }
        } else {
            foreach ([
                $table.'_CreatedBy_foreign',
                $table+'_ModifiedBy_foreign',
                $table.'_DeletedBy_foreign',
                't_thirdpartytype_thirdparties_createdby_foreign',
                't_thirdpartytype_thirdparties_modifiedby_foreign',
                't_thirdpartytype_thirdparties_deletedby_foreign',
            ] as $fk) {
                try { DB::statement('ALTER TABLE `'.$table.'` DROP FOREIGN KEY `'.$fk.'`'); } catch (\Throwable $e) {}
            }
        }

        // Make audit FKs nullable (to not block inserts during transition)
        Schema::table($table, function (Blueprint $blueprint) use ($table) {
            if (Schema::hasColumn($table, 'CreatedBy')) {
                $blueprint->unsignedBigInteger('CreatedBy')->nullable()->change();
            }
            if (Schema::hasColumn($table, 'ModifiedBy')) {
                $blueprint->unsignedBigInteger('ModifiedBy')->nullable()->change();
            }
            if (Schema::hasColumn($table, 'DeletedBy')) {
                $blueprint->unsignedBigInteger('DeletedBy')->nullable()->change();
            }
        });

        // Re-create FKs to t_ThirdPartyUsers(Id)
    Schema::table($table, function (Blueprint $blueprint) {
            if (Schema::hasTable('t_ThirdPartyUsers')) {
                if (Schema::hasColumn('t_ThirdPartyType_ThirdParties', 'CreatedBy')) {
            $blueprint->foreign('CreatedBy', 'fk_tptp_createdby_tpu')->references('Id')->on('t_ThirdPartyUsers');
                }
                if (Schema::hasColumn('t_ThirdPartyType_ThirdParties', 'ModifiedBy')) {
            $blueprint->foreign('ModifiedBy', 'fk_tptp_modifiedby_tpu')->references('Id')->on('t_ThirdPartyUsers');
                }
                if (Schema::hasColumn('t_ThirdPartyType_ThirdParties', 'DeletedBy')) {
            $blueprint->foreign('DeletedBy', 'fk_tptp_deletedby_tpu')->references('Id')->on('t_ThirdPartyUsers');
                }
            }
        });
    }

    public function down(): void
    {
        $table = 't_ThirdPartyType_ThirdParties';
        if (!Schema::hasTable($table)) { return; }

        // Drop current FKs
        $driver = DB::getDriverName();
        if ($driver === 'sqlsrv') {
            $constraints = DB::select("SELECT f.name AS constraint_name FROM sys.foreign_keys f JOIN sys.tables t ON f.parent_object_id = t.object_id WHERE t.name = ?", [$table]);
            foreach ($constraints as $c) {
                $name = strtolower($c->constraint_name);
                if (str_contains($name, 'createdby') || str_contains($name, 'modifiedby') || str_contains($name, 'deletedby')) {
                    try { DB::statement('ALTER TABLE '.$table.' DROP CONSTRAINT '.$c->constraint_name); } catch (\Throwable $e) { /* ignore */ }
                }
            }
            foreach ([
                'fk_tptp_createdby_tpu',
                'fk_tptp_modifiedby_tpu',
                'fk_tptp_deletedby_tpu',
                't_ThirdPartyType_ThirdParties_CreatedBy_foreign',
                't_ThirdPartyType_ThirdParties_ModifiedBy_foreign',
                't_ThirdPartyType_ThirdParties_DeletedBy_foreign',
                't_thirdpartytype_thirdparties_createdby_foreign',
                't_thirdpartytype_thirdparties_modifiedby_foreign',
                't_thirdpartytype_thirdparties_deletedby_foreign',
            ] as $fk) {
                try { DB::statement("IF OBJECT_ID(N'{$fk}', N'F') IS NOT NULL ALTER TABLE {$table} DROP CONSTRAINT {$fk}"); } catch (\Throwable $e) { /* ignore */ }
            }
        } else {
            foreach ([
                $table.'_CreatedBy_foreign',
                $table.'_ModifiedBy_foreign',
                $table.'_DeletedBy_foreign',
                'fk_tptp_createdby_tpu',
                'fk_tptp_modifiedby_tpu',
                'fk_tptp_deletedby_tpu',
                't_thirdpartytype_thirdparties_createdby_foreign',
                't_thirdpartytype_thirdparties_modifiedby_foreign',
                't_thirdpartytype_thirdparties_deletedby_foreign',
            ] as $fk) {
                try { DB::statement('ALTER TABLE `'.$table.'` DROP FOREIGN KEY `'.$fk+'`'); } catch (\Throwable $e) {}
            }
        }

        // Repoint to t_Users and make non-nullable again (if desired)
    Schema::table($table, function (Blueprint $blueprint) {
            if (Schema::hasTable('t_Users')) {
                if (Schema::hasColumn('t_ThirdPartyType_ThirdParties', 'CreatedBy')) {
            $blueprint->foreign('CreatedBy', 't_thirdpartytype_thirdparties_createdby_foreign')->references('Id')->on('t_Users')->nullOnDelete();
                }
                if (Schema::hasColumn('t_ThirdPartyType_ThirdParties', 'ModifiedBy')) {
            $blueprint->foreign('ModifiedBy', 't_thirdpartytype_thirdparties_modifiedby_foreign')->references('Id')->on('t_Users')->nullOnDelete();
                }
                if (Schema::hasColumn('t_ThirdPartyType_ThirdParties', 'DeletedBy')) {
            $blueprint->foreign('DeletedBy', 't_thirdpartytype_thirdparties_deletedby_foreign')->references('Id')->on('t_Users')->nullOnDelete();
                }
            }
        });
    }
};
