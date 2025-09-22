<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_PrequalificationRounds', function (Blueprint $table) {
            $table->string('Status', 2)->default('O')->nullable(false)->change();
        });

        DB::statement("
            ALTER TABLE t_PrequalificationRounds
            ADD CONSTRAINT CK_PrequalificationRounds_Status
            CHECK (Status IN ('O', 'CL'))
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE t_PrequalificationRounds
            DROP CONSTRAINT CK_PrequalificationRounds_Status
        ");

        Schema::table('t_PrequalificationRounds', function (Blueprint $table) {
            $table->string('Status')->nullable()->change();
        });
    }
};
