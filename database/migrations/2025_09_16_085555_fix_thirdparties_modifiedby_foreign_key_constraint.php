<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop the incorrect foreign key constraint that references t_ThirdPartyUsers
        DB::statement("IF OBJECT_ID(N't_thirdparties_modifiedby_foreign', N'F') IS NOT NULL ALTER TABLE t_ThirdParties DROP CONSTRAINT t_thirdparties_modifiedby_foreign");

        // Re-create the foreign key constraint to reference t_Users instead
        Schema::table('t_ThirdParties', function (Blueprint $table) {
            $table->foreign('ModifiedBy', 't_thirdparties_modifiedby_foreign')
                ->references('Id')
                ->on('t_Users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        // Drop the corrected foreign key constraint
        DB::statement("IF OBJECT_ID(N't_thirdparties_modifiedby_foreign', N'F') IS NOT NULL ALTER TABLE t_ThirdParties DROP CONSTRAINT t_thirdparties_modifiedby_foreign");

        // Re-create the incorrect constraint (for rollback purposes)
        Schema::table('t_ThirdParties', function (Blueprint $table) {
            $table->foreign('ModifiedBy', 't_thirdparties_modifiedby_foreign')
                ->references('Id')
                ->on('t_ThirdPartyUsers')
                ->onDelete('set null');
        });
    }
};
