<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Step 1: Drop existing primary key
        DB::statement("ALTER TABLE t_ModelRoles DROP CONSTRAINT model_has_roles_role_model_type_primary");

        // Step 2: Add new composite primary key including BranchId
        DB::statement("ALTER TABLE t_ModelRoles ADD CONSTRAINT pk_modelroles PRIMARY KEY (role_id, model_id, model_type, BranchId)");
    }

    public function down(): void
    {
        // Rollback: Drop new PK and re-add old one
        DB::statement("ALTER TABLE t_ModelRoles DROP CONSTRAINT pk_modelroles");

        DB::statement("ALTER TABLE t_ModelRoles ADD CONSTRAINT model_has_roles_role_model_type_primary PRIMARY KEY (role_id, model_id, model_type)");
    }
};
