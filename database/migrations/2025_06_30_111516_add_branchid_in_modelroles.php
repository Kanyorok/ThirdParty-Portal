<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_ModelRoles', function (Blueprint $table) {
            $table->foreignId('BranchId')
                ->nullable()
                ->constrained('t_Branches', 'Id')
                ->onDelete('set null'); // optional: handle deletions
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ModelRoles', function (Blueprint $table) {
            // Drop foreign key first, then the column
            $table->dropForeign(['BranchId']);
            $table->dropColumn('BranchId');
        });
    }
};
