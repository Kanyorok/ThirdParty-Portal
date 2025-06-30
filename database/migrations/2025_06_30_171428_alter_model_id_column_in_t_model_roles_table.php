<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Drop the dependent index
        DB::statement("DROP INDEX model_has_roles_model_id_model_type_index ON t_ModelRoles");

        // Step 2: Alter the column type
        DB::statement("ALTER TABLE t_ModelRoles ALTER COLUMN model_id VARCHAR(50)");

        // Step 3: Recreate the index (same structure)
        DB::statement("CREATE INDEX model_has_roles_model_id_model_type_index ON t_ModelRoles (model_id, model_type)");
    }

    public function down(): void
    {
        // Step 1: Drop the recreated index
        DB::statement("DROP INDEX model_has_roles_model_id_model_type_index ON t_ModelRoles");

        // Step 2: Revert model_id to BIGINT
        DB::statement("ALTER TABLE t_ModelRoles ALTER COLUMN model_id BIGINT");

        // Step 3: Recreate the original index
        DB::statement("CREATE INDEX model_has_roles_model_id_model_type_index ON t_ModelRoles (model_id, model_type)");
    }
};
