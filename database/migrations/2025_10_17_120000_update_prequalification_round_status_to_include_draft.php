<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure Status column exists and set default to 'D' (Draft)
        Schema::table('t_PrequalificationRounds', function (Blueprint $table) {
            $table->string('Status', 2)->default('D')->nullable(false)->change();
        });

        // Drop existing check constraint if present, then add new one including 'D'
        try {
            DB::statement("ALTER TABLE t_PrequalificationRounds DROP CONSTRAINT CK_PrequalificationRounds_Status");
        } catch (\Throwable $e) {
            // Constraint may not exist on some environments; ignore
        }

        DB::statement("ALTER TABLE t_PrequalificationRounds ADD CONSTRAINT CK_PrequalificationRounds_Status CHECK (Status IN ('D','O','CL'))");
    }

    public function down(): void
    {
        // Revert default to 'O' and constraint to only 'O' and 'CL'
        try {
            DB::statement("ALTER TABLE t_PrequalificationRounds DROP CONSTRAINT CK_PrequalificationRounds_Status");
        } catch (\Throwable $e) {}

        Schema::table('t_PrequalificationRounds', function (Blueprint $table) {
            $table->string('Status', 2)->default('O')->nullable(false)->change();
        });

        DB::statement("ALTER TABLE t_PrequalificationRounds ADD CONSTRAINT CK_PrequalificationRounds_Status CHECK (Status IN ('O','CL'))");
    }
};
